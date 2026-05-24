<script setup>
import { inject } from "vue";
import { useForm } from "@inertiajs/vue3";

import Button from "../../Components/UI/BaseButton.vue";

const route = inject("route");

const form = useForm({
    nama_satuan: "",
});

const submit = () => {
    form.post(route("satuan.store"), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title">Tambah Satuan</h5>
            <form @submit.prevent="submit">
                <div class="mb-3">
                    <label class="form-label">Nama Satuan</label>
                    <input
                        v-model="form.nama_satuan"
                        type="text"
                        class="form-control"
                        :class="{
                            'is-invalid': form.errors.nama_satuan,
                        }"
                        placeholder="Masukkan nama satuan"
                    />
                    <small v-if="form.errors.nama_satuan" class="text-danger">
                        {{ form.errors.nama_satuan }}
                    </small>
                </div>
                <div class="mb-3">
                    <Button
                        :disabled="form.processing"
                        variant="primary"
                        type="submit"
                    >
                        <span v-if="form.processing"> Menyimpan... </span>
                        <span v-else> Simpan </span>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
