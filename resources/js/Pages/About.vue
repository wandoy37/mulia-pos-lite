<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="container py-5">
        <h1 class="mb-4 text-success">Halaman About</h1>

        <p class="mb-4">
            Ini contoh perpindahan halaman menggunakan Inertia.js
        </p>

        <Link :href="route('home')" class="btn btn-success">
            Kembali ke Home
        </Link>
    </div>
</template>
