<script setup>
import { inject } from "vue";

import { Link } from "@inertiajs/vue3";

const route = inject("route");

function isActive(name) {
    if (route().current(name)) return true;
    return false;
}

function isDataMasterActive() {
    return ["satuan.*", "supplier.*", "produk.*"].some((p) =>
        route().current(p),
    );
}
</script>
<template>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <div
                    class="text-primary"
                    style="font-size: 1.5rem; font-weight: bold"
                >
                    POS
                    <i>Lite</i>
                </div>
            </a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item mx-4">
                        <Link
                            class="nav-link"
                            :class="{ active: isActive('dashboard') }"
                            aria-current="page"
                            :href="route('dashboard')"
                            >Dashboard</Link
                        >
                    </li>
                    <li class="nav-item dropdown mx-4">
                        <Link
                            class="nav-link dropdown-toggle"
                            :class="{ active: isDataMasterActive() }"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            Data Master
                        </Link>
                        <ul class="dropdown-menu">
                            <li>
                                <Link
                                    class="dropdown-item"
                                    :class="{ active: isActive('satuan.*') }"
                                    :href="route('satuan.index')"
                                    >Satuan</Link
                                >
                            </li>
                            <li>
                                <Link
                                    class="dropdown-item"
                                    :class="{ active: isActive('supplier.*') }"
                                    :href="route('supplier.index')"
                                    >Supplier</Link
                                >
                            </li>
                            <li>
                                <a
                                    class="dropdown-item"
                                    :class="{ active: isActive('produk.*') }"
                                    :href="route('produk.index')"
                                    >Produk</a
                                >
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item mx-4">
                        <a class="nav-link" href="#">Pembelian</a>
                    </li>
                    <li class="nav-item mx-4">
                        <a class="nav-link" href="#">Penjualan</a>
                    </li>
                    <li class="nav-item mx-4">
                        <a class="nav-link" href="#">Laporan</a>
                    </li>
                    <li class="nav-item mx-4">
                        <div class="dropdown">
                            <a
                                class="btn btn-primary dropdown-toggle"
                                href="#"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                Profile
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        Username
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider" /></li>
                                <li>
                                    <a
                                        class="dropdown-item text-danger"
                                        href="#"
                                    >
                                        <i class="bi bi-escape"></i>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>
