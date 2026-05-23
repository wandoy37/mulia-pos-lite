<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="container py-5">
        <h1 class="mb-4 text-primary">Laravel 13 + Vue 3 + Inertia.js</h1>

        <p class="mb-4">Setup berhasil 🚀</p>

        <Link :href="route('about')" class="btn btn-primary">
            Ke Halaman About
        </Link>
    </div>
</template>
