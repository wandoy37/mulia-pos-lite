<script setup>
import { inject } from "vue";
import { router } from "@inertiajs/vue3";
import BaseButton from "../../Components/UI/BaseButton.vue";

const route = inject("route");

const props = defineProps({
    satuans: {
        type: Array,
        default: () => [],
    },
});

const hapus = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus satuan ini?")) {
        router.delete(route("satuan.destroy", id));
    }
};
</script>

<template>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nama Satuan</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="satuans.length === 0">
                <td colspan="3" class="text-center text-muted">
                    Belum ada data satuan
                </td>
            </tr>
            <tr v-for="(item, index) in satuans" :key="item.id">
                <th scope="row">{{ index + 1 }}</th>
                <td>{{ item.nama_satuan }}</td>
                <td class="d-flex gap-2">
                    <BaseButton
                        variant="warning"
                        size="sm"
                        @click="
                            router.get(route('satuan.edit', item.id))
                        "
                    >
                        Edit
                    </BaseButton>
                    <BaseButton
                        variant="danger"
                        size="sm"
                        @click="hapus(item.id)"
                    >
                        Hapus
                    </BaseButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>
