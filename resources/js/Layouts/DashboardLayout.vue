<script setup>
import Navbar from "../Components/Layout/Navbar.vue";
</script>

<template>
    <Navbar />
    <main class="container mt-4">
        <slot />
    </main>
</template>
