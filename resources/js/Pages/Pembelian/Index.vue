<script setup>
import { inject, ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Modal } from "bootstrap";
import DashboardLayout from "../../Layouts/DashboardLayout.vue";

const route = inject("route");
const page = usePage();

const props = defineProps({
    pembelians: { type: Object, default: () => ({}) },
    nextRef: { type: String, default: "" },
    filters: { type: Object, default: () => ({}) },
});

const supplierOptions = ref([]);
const searchLoading = ref(false);

const selectedSupplier = ref(null);

watch(selectedSupplier, (val) => {
    form.supplier_id = val?.id ?? "";
});

const onSearchSupplier = (search, loading) => {
    if (search.length < 3) {
        supplierOptions.value = [];
        loading(false);

        return;
    }

    loading(true);
    searchLoading.value = true;

    fetch(`/supplier/search?q=${encodeURIComponent(search)}`)
        .then((res) => res.json())
        .then((data) => {
            supplierOptions.value = data;
        })
        .finally(() => {
            loading(false);
            searchLoading.value = false;
        });
};

const produkSearchResults = ref([]);
const produkSearchLoading = ref(false);
const selectedProduks = ref([]);

watch(
    selectedProduks,
    (list) => {
        list.forEach((produk, i) => {
            if (produk && form.details[i]) {
                form.details[i].produk_id = produk.id;
                if (produk.harga_beli_terakhir) {
                    form.details[i].harga_satuan = Number(
                        produk.harga_beli_terakhir,
                    );
                }
            }
        });
    },
    { deep: true },
);

const onSearchProduk = (search, loading) => {
    if (search.length < 3) {
        produkSearchResults.value = [];
        loading(false);

        return;
    }

    loading(true);
    produkSearchLoading.value = true;

    fetch(`/produk/search?q=${encodeURIComponent(search)}`)
        .then((res) => res.json())
        .then((data) => {
            produkSearchResults.value = data;
        })
        .finally(() => {
            loading(false);
            produkSearchLoading.value = false;
        });
};

const flash = computed(() => page.props.flash);
const search = ref(props.filters.search || "");

const form = useForm({
    supplier_id: "",
    no_refrensi: "",
    tanggal_pembelian: "",
    details: [{ produk_id: "", qty: 1, harga_satuan: 0 }],
});

const modalRef = ref(null);
const detailModalRef = ref(null);
const editingId = ref(null);
const viewingDetail = ref(null);
let bsModal = null;
let bsDetailModal = null;

const isEdit = computed(() => editingId.value !== null);
const modalTitle = computed(() =>
    isEdit.value ? "Edit Pembelian" : "Tambah Pembelian",
);

const totalHarga = computed(() =>
    form.details.reduce(
        (sum, d) => sum + (Number(d.qty) || 0) * (Number(d.harga_satuan) || 0),
        0,
    ),
);

const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const openCreateModal = () => {
    editingId.value = null;
    form.reset();
    form.no_refrensi = props.nextRef;
    form.details = [{ produk_id: "", qty: 1, harga_satuan: 0 }];
    form.clearErrors();
    supplierOptions.value = [];
    selectedSupplier.value = null;
    selectedProduks.value = [null];
    bsModal.show();
};

const openEditModal = async (pembelian) => {
    editingId.value = pembelian.id;
    form.clearErrors();
    form.supplier_id = pembelian.supplier_id;
    form.no_refrensi = pembelian.no_refrensi;
    selectedSupplier.value = {
        id: pembelian.supplier_id,
        nama_supplier: pembelian.supplier?.nama_supplier ?? "",
    };
    form.tanggal_pembelian = pembelian.tanggal_pembelian;

    try {
        const response = await fetch(`/pembelian/${pembelian.id}/detail`);
        const data = await response.json();
        form.details = data.details.map((d) => ({
            produk_id: d.produk_id,
            qty: d.qty,
            harga_satuan: d.harga_satuan,
        }));
        selectedProduks.value = data.details.map((d) => ({
            id: d.produk_id,
            nama_produk: d.nama_produk,
            harga_beli_terakhir: d.harga_beli_terakhir,
        }));
    } catch {
        form.details = [{ produk_id: "", qty: 1, harga_satuan: 0 }];
        selectedProduks.value = [null];
    }

    bsModal.show();
};

const openDetailModal = (pembelian) => {
    viewingDetail.value = pembelian;
    bsDetailModal.show();
};

const closeModal = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    selectedProduks.value = [null];
    bsModal.hide();
};

const addDetailRow = () => {
    form.details.push({ produk_id: "", qty: 1, harga_satuan: 0 });
    selectedProduks.value.push(null);
};

const removeDetailRow = (index) => {
    if (form.details.length > 1) {
        form.details.splice(index, 1);
        selectedProduks.value.splice(index, 1);
    }
};

const submit = () => {
    if (editingId.value) {
        form.put(route("pembelian.update", editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("pembelian.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const hapus = (pembelian) => {
    if (confirm("Apakah Anda yakin ingin menghapus pembelian ini?")) {
        router.delete(route("pembelian.destroy", pembelian.id));
    }
};

const goToPage = (link) => {
    if (!link.url || link.active) return;
    const url = new URL(link.url);
    const pageParam = url.searchParams.get("page");
    router.get(
        route("pembelian.index"),
        { search: search.value, page: pageParam },
        { preserveState: true, preserveScroll: true },
    );
};

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route("pembelian.index"),
            { search: search.value },
            { preserveState: true, replace: true },
        );
    }, 300);
});

let debounceTimer = null;

onMounted(() => {
    if (modalRef.value) {
        bsModal = new Modal(modalRef.value, { backdrop: "static" });
    }
    if (detailModalRef.value) {
        bsDetailModal = new Modal(detailModalRef.value, { backdrop: "static" });
    }
});

onBeforeUnmount(() => {
    if (bsModal) {
        bsModal.dispose();
        bsModal = null;
    }
    if (bsDetailModal) {
        bsDetailModal.dispose();
        bsDetailModal = null;
    }
});
</script>

<template>
    <DashboardLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Pembelian</h1>
                <p class="text-muted mb-0">
                    Daftar transaksi pembelian barang.
                </p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">
                + Tambah Pembelian
            </button>
        </div>

        <div
            v-if="flash.success"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            {{ flash.success }}
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="currentColor"
                                    viewBox="0 0 16 16"
                                >
                                    <path
                                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"
                                    />
                                </svg>
                            </span>
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Cari no. refrensi atau supplier..."
                            />
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px">No</th>
                                <th>No. Refrensi</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Total Harga</th>
                                <th style="width: 220px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-if="
                                    !pembelians.data ||
                                    pembelians.data.length === 0
                                "
                            >
                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >
                                    Belum ada data pembelian
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in pembelians.data"
                                :key="item.id"
                            >
                                <th scope="row">
                                    {{ pembelians.from + index }}
                                </th>
                                <td>{{ item.no_refrensi }}</td>
                                <td>{{ item.tanggal_pembelian }}</td>
                                <td>
                                    {{ item.supplier?.nama_supplier ?? "-" }}
                                </td>
                                <td>{{ formatRupiah(item.total_harga) }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button
                                            class="btn btn-info btn-sm text-white"
                                            @click="openDetailModal(item)"
                                        >
                                            Detail
                                        </button>
                                        <button
                                            class="btn btn-warning btn-sm"
                                            @click="openEditModal(item)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            class="btn btn-danger btn-sm"
                                            @click="hapus(item)"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="pembelians.data && pembelians.data.length > 0"
                    class="d-flex justify-content-between align-items-center mt-3"
                >
                    <small class="text-muted">
                        Menampilkan {{ pembelians.from }} -{{
                            pembelians.to
                        }}
                        dari {{ pembelians.total }} data
                    </small>
                    <nav v-if="pembelians.last_page > 1">
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                v-for="(link, i) in pembelians.links"
                                :key="i"
                                class="page-item"
                                :class="{
                                    disabled: !link.url,
                                    active: link.active,
                                }"
                            >
                                <a
                                    href="#"
                                    class="page-link"
                                    v-html="link.label"
                                    @click.prevent="goToPage(link)"
                                ></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit Pembelian -->
        <div ref="modalRef" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ modalTitle }}</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeModal"
                        ></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">
                                        No. Refrensi
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.no_refrensi"
                                        type="text"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                form.errors.no_refrensi,
                                        }"
                                        disabled
                                    />
                                    <small
                                        v-if="form.errors.no_refrensi"
                                        class="text-danger"
                                    >
                                        {{ form.errors.no_refrensi }}
                                    </small>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">
                                        Tanggal
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.tanggal_pembelian"
                                        type="date"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                form.errors.tanggal_pembelian,
                                        }"
                                    />
                                    <small
                                        v-if="form.errors.tanggal_pembelian"
                                        class="text-danger"
                                    >
                                        {{ form.errors.tanggal_pembelian }}
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Supplier
                                        <span class="text-danger">*</span>
                                    </label>
                                    <v-select
                                        v-model="selectedSupplier"
                                        :options="supplierOptions"
                                        :filterable="false"
                                        label="nama_supplier"
                                        placeholder="Cari supplier (min. 3 huruf)..."
                                        @search="onSearchSupplier"
                                        :class="{
                                            'is-invalid':
                                                form.errors.supplier_id,
                                        }"
                                    >
                                        <template #no-options>
                                            {{
                                                searchLoading
                                                    ? "Mencari..."
                                                    : "Ketik minimal 3 huruf"
                                            }}
                                        </template>
                                    </v-select>
                                    <small
                                        v-if="form.errors.supplier_id"
                                        class="text-danger"
                                    >
                                        {{ form.errors.supplier_id }}
                                    </small>
                                </div>
                            </div>

                            <hr />

                            <div
                                class="d-flex justify-content-between align-items-center mb-2"
                            >
                                <label class="form-label fw-bold mb-0">
                                    Detail Item
                                </label>
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    @click="addDetailRow"
                                >
                                    + Tambah Item
                                </button>
                            </div>

                            <div
                                v-if="form.errors['details.0']"
                                class="text-danger small mb-2"
                            >
                                {{ form.errors["details.0"] }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 35%">Produk</th>
                                            <th style="width: 15%">Qty</th>
                                            <th style="width: 25%">
                                                Harga Satuan
                                            </th>
                                            <th style="width: 20%">Subtotal</th>
                                            <th style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(detail, i) in form.details"
                                            :key="i"
                                        >
                                            <td>
                                                <v-select
                                                    v-model="selectedProduks[i]"
                                                    :options="
                                                        produkSearchResults
                                                    "
                                                    :filterable="false"
                                                    label="nama_produk"
                                                    placeholder="Cari produk (min. 3 huruf)..."
                                                    @search="onSearchProduk"
                                                >
                                                    <template #no-options>
                                                        {{
                                                            produkSearchLoading
                                                                ? "Mencari..."
                                                                : "Ketik minimal 3 huruf"
                                                        }}
                                                    </template>
                                                </v-select>
                                                <small
                                                    v-if="
                                                        form.errors[
                                                            `details.${i}.produk_id`
                                                        ]
                                                    "
                                                    class="text-danger"
                                                >
                                                    {{
                                                        form.errors[
                                                            `details.${i}.produk_id`
                                                        ]
                                                    }}
                                                </small>
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="detail.qty"
                                                    type="number"
                                                    min="1"
                                                    class="form-control form-control-sm"
                                                    :class="{
                                                        'is-invalid':
                                                            form.errors[
                                                                `details.${i}.qty`
                                                            ],
                                                    }"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="
                                                        detail.harga_satuan
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="100"
                                                    class="form-control form-control-sm"
                                                    :class="{
                                                        'is-invalid':
                                                            form.errors[
                                                                `details.${i}.harga_satuan`
                                                            ],
                                                    }"
                                                />
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatRupiah(
                                                        (Number(detail.qty) ||
                                                            0) *
                                                            (Number(
                                                                detail.harga_satuan,
                                                            ) || 0),
                                                    )
                                                }}
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    @click="removeDetailRow(i)"
                                                    :disabled="
                                                        form.details.length <= 1
                                                    "
                                                >
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="text-end fw-bold"
                                            >
                                                Total Harga
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ formatRupiah(totalHarga) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="closeModal"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                <span
                                    v-if="form.processing"
                                    class="spinner-border spinner-border-sm me-1"
                                ></span>
                                {{
                                    form.processing ? "Menyimpan..." : "Simpan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Detail Pembelian -->
        <div ref="detailModalRef" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pembelian</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <div class="modal-body" v-if="viewingDetail">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted">No. Refrensi</small>
                                <p class="fw-bold mb-0">
                                    {{ viewingDetail.no_refrensi }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Tanggal</small>
                                <p class="fw-bold mb-0">
                                    {{ viewingDetail.tanggal_pembelian }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Supplier</small>
                                <p class="fw-bold mb-0">
                                    {{
                                        viewingDetail.supplier?.nama_supplier ??
                                        "-"
                                    }}
                                </p>
                            </div>
                        </div>
                        <hr />
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="d in viewingDetail.detail_pembelian"
                                    :key="d.id"
                                >
                                    <td>
                                        {{ d.produk?.nama_produk ?? "-" }}
                                    </td>
                                    <td>{{ d.qty }}</td>
                                    <td>{{ formatRupiah(d.harga_satuan) }}</td>
                                    <td class="text-end">
                                        {{
                                            formatRupiah(d.qty * d.harga_satuan)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">
                                        Total Harga
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{
                                            formatRupiah(
                                                viewingDetail.total_harga,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
