<script setup>
import { inject, ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import DashboardLayout from "../../Layouts/DashboardLayout.vue";

const route = inject("route");
const page = usePage();

const props = defineProps({
    produk: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const flash = computed(() => page.props.flash);
const search = ref(props.filters.search || "");

let debounceTimer = null;

watch(search, (value) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route("produk.index"),
            { search: value },
            { preserveState: true, replace: true },
        );
    }, 300);
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const stokBadgeClass = (stok) => {
    if (stok > 10) return "bg-success text-white";
    if (stok >= 1) return "bg-warning text-dark";
    return "bg-danger";
};

const stokBadgeIcon = (stok) => {
    if (stok > 10) return "bi-check-circle-fill";
    if (stok >= 1) return "bi-exclamation-circle-fill";
    return "bi-x-circle-fill";
};

const confirmDelete = (id) => {
    if (confirm("Yakin hapus produk ini?")) {
        router.delete(route("produk.destroy", id));
    }
};

const goToPage = (link) => {
    if (!link.url || link.active) return;
    const url = new URL(link.url);
    const pageParam = url.searchParams.get("page");
    router.get(
        route("produk.index"),
        { search: search.value, page: pageParam },
        { preserveState: true, preserveScroll: true },
    );
};
</script>

<template>
    <DashboardLayout>
        <!-- ── Page Heading ── -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">Kelola Produk</h1>
                <p class="text-muted mb-0">
                    Daftar produk yang terdaftar dalam sistem.
                </p>
            </div>
        </div>

        <!-- ── Flash Message ── -->
        <div
            v-if="flash.success"
            class="alert alert-success alert-dismissible fade show mb-4"
            role="alert"
        >
            <i class="bi bi-check-circle-fill me-2"></i>{{ flash.success }}
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

        <!-- ── Table Card ── -->
        <div class="table-card">
            <!-- Card Header -->
            <div class="table-card-header">
                <div>
                    <h5>Daftar Produk</h5>
                    <div class="subtitle">
                        Menampilkan {{ produk.total ?? 0 }} produk terdaftar
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Search -->
                    <div class="search-wrapper">
                        <i class="bi bi-search"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari produk..."
                        />
                    </div>
                    <!-- Tambah -->
                    <button
                        class="btn btn-primary d-flex align-items-center gap-1"
                        style="
                            border-radius: 10px;
                            height: 38px;
                            padding: 0 1rem;
                            font-size: 0.85rem;
                            font-weight: 600;
                        "
                        @click="router.get(route('produk.create'))"
                    >
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table align-middle" id="productTable">
                    <thead>
                        <tr>
                            <th style="width: 60px">No</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th style="width: 100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Empty state row -->
                        <tr v-if="!produk.data || produk.data.length === 0">
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox d-block mb-2"></i>
                                    <div class="fw-semibold">
                                        Belum ada data produk
                                    </div>
                                    <div
                                        style="
                                            font-size: 0.8rem;
                                            margin-top: 4px;
                                        "
                                    >
                                        Klik "Tambah Produk" untuk menambahkan
                                        produk baru.
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Data rows -->
                        <tr v-for="(item, index) in produk.data" :key="item.id">
                            <!-- No -->
                            <td class="col-no">
                                {{
                                    String(produk.from + index).padStart(2, "0")
                                }}
                            </td>

                            <!-- Nama Produk -->
                            <td>
                                <div class="product-cell">
                                    <div>
                                        <div class="product-name">
                                            {{ item.nama_produk }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Satuan -->
                            <td>
                                <span
                                    style="
                                        font-size: 0.85rem;
                                        color: var(--text-muted);
                                    "
                                >
                                    {{ item.satuan?.nama_satuan ?? "—" }}
                                </span>
                            </td>

                            <!-- Harga Beli -->
                            <td>
                                <span class="price-label">{{
                                    formatRupiah(item.harga_beli_terakhir)
                                }}</span>
                            </td>

                            <!-- Harga Jual -->
                            <td>
                                <span class="price-label">{{
                                    formatRupiah(item.harga_jual)
                                }}</span>
                            </td>

                            <!-- Stok -->
                            <td>
                                <span
                                    class="status-badge"
                                    :class="stokBadgeClass(item.stok_saat_ini)"
                                >
                                    <i
                                        class="bi"
                                        :class="
                                            stokBadgeIcon(item.stok_saat_ini)
                                        "
                                    ></i>
                                    {{ item.stok_saat_ini }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td>
                                <div class="action-group">
                                    <button
                                        class="btn btn-action btn-edit"
                                        title="Edit produk"
                                        @click="
                                            router.get(
                                                route('produk.edit', item.id),
                                            )
                                        "
                                    >
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button
                                        class="btn btn-action btn-delete"
                                        title="Hapus produk"
                                        @click="confirmDelete(item.id)"
                                    >
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Card Footer — info + pagination -->
            <div
                v-if="produk.data && produk.data.length > 0"
                class="table-card-footer"
            >
                <small>
                    Menampilkan {{ produk.from }}–{{ produk.to }} dari
                    {{ produk.total }} entri
                </small>

                <nav v-if="produk.last_page > 1">
                    <ul class="pagination mb-0">
                        <li
                            v-for="(link, i) in produk.links"
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
        <!-- /table-card -->
    </DashboardLayout>
</template>
