<script setup>
import { inject, ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Modal } from "bootstrap";
import DashboardLayout from "../../Layouts/DashboardLayout.vue";

const route = inject("route");

const page = usePage();

const props = defineProps({
    suppliers: {
        type: Object,
        default: () => ({}),
    },
    search: {
        type: String,
        default: "",
    },
});

const flash = computed(() => page.props.flash);

const form = useForm({
    nama_supplier: "",
    kontak: "",
});

const modalRef = ref(null);
const editingId = ref(null);
let bsModal = null;

const searchInput = ref(props.search);
let debounceTimer = null;

const isEdit = computed(() => editingId.value !== null);
const modalTitle = computed(() =>
    isEdit.value ? "Edit Supplier" : "Tambah Supplier",
);

const openCreateModal = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    bsModal.show();
};

const openEditModal = (supplier) => {
    editingId.value = supplier.id;
    form.clearErrors();
    form.nama_supplier = supplier.nama_supplier;
    form.kontak = supplier.kontak ?? "";
    bsModal.show();
};

const closeModal = () => {
    if (editingId.value) {
        editingId.value = null;
    }
    form.reset();
    form.clearErrors();
    bsModal.hide();
};

const submit = () => {
    if (editingId.value) {
        form.put(route("supplier.update", editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("supplier.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const hapus = (supplier) => {
    if (confirm("Apakah Anda yakin ingin menghapus supplier ini?")) {
        router.delete(route("supplier.destroy", supplier.id));
    }
};

const goToPage = (link) => {
    if (!link.url || link.active) return;

    const url = new URL(link.url);
    const page = url.searchParams.get("page");

    router.get(route("supplier.index"), {
        search: searchInput.value,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const onSearchInput = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route("supplier.index"), {
            search: searchInput.value,
            page: 1,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

onMounted(() => {
    if (modalRef.value) {
        bsModal = new Modal(modalRef.value, {
            backdrop: "static",
        });
    }
});

onBeforeUnmount(() => {
    if (bsModal) {
        bsModal.dispose();
        bsModal = null;
    }
});
</script>

<template>
    <DashboardLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Supplier</h1>
                <p class="text-muted mb-0">Daftar supplier yang terdaftar dalam sistem.</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">
                + Tambah Supplier
            </button>
        </div>

        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                            </span>
                            <input
                                v-model="searchInput"
                                type="text"
                                class="form-control"
                                placeholder="Cari nama supplier atau kontak..."
                                @input="onSearchInput"
                            />
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 60px">No</th>
                                <th scope="col">Nama Supplier</th>
                                <th scope="col">Kontak</th>
                                <th scope="col" style="width: 160px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!suppliers.data || suppliers.data.length === 0">
                                <td colspan="4" class="text-center text-muted py-4">
                                    Belum ada data supplier
                                </td>
                            </tr>
                            <tr v-for="(item, index) in suppliers.data" :key="item.id">
                                <th scope="row">{{ suppliers.from + index }}</th>
                                <td>{{ item.nama_supplier }}</td>
                                <td>{{ item.kontak ?? "-" }}</td>
                                <td>
                                    <div class="d-flex gap-2">
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

                <div v-if="suppliers.data && suppliers.data.length > 0" class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Menampilkan {{ suppliers.from }} - {{ suppliers.to }} dari {{ suppliers.total }} data
                    </small>
                    <nav v-if="suppliers.last_page > 1">
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                v-for="(link, i) in suppliers.links"
                                :key="i"
                                class="page-item"
                                :class="{
                                    'disabled': !link.url,
                                    'active': link.active,
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

        <div ref="modalRef" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
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
                            <div class="mb-3">
                                <label for="nama_supplier" class="form-label">
                                    Nama Supplier <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="nama_supplier"
                                    v-model="form.nama_supplier"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.nama_supplier }"
                                    placeholder="Masukkan nama supplier"
                                />
                                <small
                                    v-if="form.errors.nama_supplier"
                                    class="text-danger"
                                >
                                    {{ form.errors.nama_supplier }}
                                </small>
                            </div>
                            <div class="mb-3">
                                <label for="kontak" class="form-label">Kontak</label>
                                <input
                                    id="kontak"
                                    v-model="form.kontak"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.kontak }"
                                    placeholder="Nomor telepon atau kontak lainnya"
                                />
                                <small v-if="form.errors.kontak" class="text-danger">
                                    {{ form.errors.kontak }}
                                </small>
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
                                {{ form.processing ? "Menyimpan..." : "Simpan" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
