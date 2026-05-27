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
    if (stok > 10) return "bg-success";
    if (stok >= 1) return "bg-warning text-dark";
    return "bg-danger";
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Kelola Produk</h1>
                <p class="text-muted mb-0">
                    Daftar produk yang terdaftar dalam sistem.
                </p>
            </div>
            <button
                class="btn btn-warning"
                @click="router.get(route('produk.create'))"
            >
                + Tambah Produk
            </button>
        </div>

        <div
            v-if="flash.success"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            {{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="card shadow-sm p-4 bg-white">
            <div class="row mb-3">
                <div class="col-md-4">
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
                            placeholder="Cari produk..."
                        />
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 60px">No</th>
                            <th scope="col">Nama Produk</th>
                            <th scope="col">Satuan</th>
                            <th scope="col">Harga Beli</th>
                            <th scope="col">Harga Jual</th>
                            <th scope="col">Stok</th>
                            <th scope="col" style="width: 160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="!produk.data || produk.data.length === 0"
                        >
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada data produk
                            </td>
                        </tr>
                        <tr
                            v-for="(item, index) in produk.data"
                            :key="item.id"
                        >
                            <th scope="row">{{ produk.from + index }}</th>
                            <td>{{ item.nama_produk }}</td>
                            <td>{{ item.satuan?.nama_satuan ?? "-" }}</td>
                            <td>{{ formatRupiah(item.harga_beli_terakhir) }}</td>
                            <td>{{ formatRupiah(item.harga_jual) }}</td>
                            <td>
                                <span
                                    class="badge"
                                    :class="stokBadgeClass(item.stok_saat_ini)"
                                >
                                    {{ item.stok_saat_ini }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button
                                        class="btn btn-outline-warning btn-sm"
                                        @click="
                                            router.get(
                                                route('produk.edit', item.id),
                                            )
                                        "
                                    >
                                        Edit
                                    </button>
                                    <button
                                        class="btn btn-outline-danger btn-sm"
                                        @click="confirmDelete(item.id)"
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
                v-if="produk.data && produk.data.length > 0"
                class="d-flex justify-content-between align-items-center mt-3"
            >
                <small class="text-muted">
                    Menampilkan {{ produk.from }} - {{ produk.to }} dari
                    {{ produk.total }} data
                </small>
                <nav v-if="produk.last_page > 1">
                    <ul class="pagination pagination-sm mb-0">
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
    </DashboardLayout>
</template>
