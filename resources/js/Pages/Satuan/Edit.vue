<script setup>
import { inject } from "vue";
import { useForm } from "@inertiajs/vue3";
import DashboardLayout from "../../Layouts/DashboardLayout.vue";
import Button from "../../Components/UI/BaseButton.vue";

const route = inject("route");

const props = defineProps({
    satuan: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    nama_satuan: props.satuan.nama_satuan,
});

const submit = () => {
    form.put(route("satuan.update", props.satuan.id));
};
</script>

<template>
    <DashboardLayout>
        <div class="row">
            <div class="col-lg-12">
                <h1>Edit Satuan</h1>
            </div>
            <div class="col-lg-6 col-sm-12">
                <div class="card shadow">
                    <div class="card-body">
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
                                <small
                                    v-if="form.errors.nama_satuan"
                                    class="text-danger"
                                >
                                    {{ form.errors.nama_satuan }}
                                </small>
                            </div>
                            <div class="mb-3 d-flex gap-2">
                                <Button
                                    :disabled="form.processing"
                                    variant="primary"
                                    type="submit"
                                >
                                    <span v-if="form.processing">
                                    Menyimpan...
                                </span>
                                    <span v-else> Update </span>
                                </Button>
                                <Button
                                    variant="secondary"
                                    @click="
                                        () =>
                                            $inertia.get(
                                                route('satuan.index')
                                            )
                                    "
                                >
                                    Batal
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
