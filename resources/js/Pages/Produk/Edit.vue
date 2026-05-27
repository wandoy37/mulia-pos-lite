<script setup>
import { inject, ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import DashboardLayout from "../../Layouts/DashboardLayout.vue";

const route = inject("route");

const props = defineProps({
    produk: {
        type: Object,
        required: true,
    },
    satuans: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    nama_produk: props.produk.nama_produk,
    satuan_id: props.produk.satuan_id,
    harga_beli_terakhir: props.produk.harga_beli_terakhir,
    harga_jual: props.produk.harga_jual,
    stok_saat_ini: props.produk.stok_saat_ini,
});

const formatAngka = (val) => {
    const num = String(val).replace(/\D/g, "");
    return num ? Number(num).toLocaleString("id-ID") : "";
};

const parseAngka = (val) => {
    const num = String(val).replace(/\D/g, "");
    return num ? Number(num) : "";
};

const displayHargaBeli = ref(formatAngka(props.produk.harga_beli_terakhir));
const displayHargaJual = ref(formatAngka(props.produk.harga_jual));

watch(displayHargaBeli, (val) => {
    const raw = parseAngka(val);
    form.harga_beli_terakhir = raw;
    displayHargaBeli.value = formatAngka(val);
});

watch(displayHargaJual, (val) => {
    const raw = parseAngka(val);
    form.harga_jual = raw;
    displayHargaJual.value = formatAngka(val);
});

const submit = () => {
    form.put(route("produk.update", props.produk.id));
};
</script>

<template>
    <DashboardLayout>
        <div class="row">
            <div class="col-lg-12">
                <h1 class="mb-4">
                    Edit Produk: {{ produk.nama_produk }}
                </h1>
            </div>
            <div class="col-lg-6 col-sm-12">
                <div class="card shadow-sm p-4 bg-white">
                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <label class="form-label">
                                Nama Produk <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.nama_produk"
                                type="text"
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors.nama_produk,
                                }"
                                placeholder="Masukkan nama produk"
                            />
                            <small
                                v-if="form.errors.nama_produk"
                                class="text-danger"
                            >
                                {{ form.errors.nama_produk }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Satuan <span class="text-danger">*</span>
                            </label>
                            <select
                                v-model="form.satuan_id"
                                class="form-select"
                                :class="{
                                    'is-invalid': form.errors.satuan_id,
                                }"
                            >
                                <option value="" disabled>
                                    -- Pilih Satuan --
                                </option>
                                <option
                                    v-for="satuan in satuans"
                                    :key="satuan.id"
                                    :value="satuan.id"
                                >
                                    {{ satuan.nama_satuan }}
                                </option>
                            </select>
                            <small
                                v-if="form.errors.satuan_id"
                                class="text-danger"
                            >
                                {{ form.errors.satuan_id }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Harga Beli Terakhir
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    v-model="displayHargaBeli"
                                    type="text"
                                    inputmode="numeric"
                                    class="form-control"
                                    :class="{
                                        'is-invalid':
                                            form.errors.harga_beli_terakhir,
                                    }"
                                    placeholder="0"
                                />
                            </div>
                            <small
                                v-if="form.errors.harga_beli_terakhir"
                                class="text-danger"
                            >
                                {{ form.errors.harga_beli_terakhir }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Harga Jual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    v-model="displayHargaJual"
                                    type="text"
                                    inputmode="numeric"
                                    class="form-control"
                                    :class="{
                                        'is-invalid': form.errors.harga_jual,
                                    }"
                                    placeholder="0"
                                />
                            </div>
                            <small
                                v-if="form.errors.harga_jual"
                                class="text-danger"
                            >
                                {{ form.errors.harga_jual }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Stok Saat Ini
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.stok_saat_ini"
                                type="number"
                                class="form-control"
                                :class="{
                                    'is-invalid': form.errors.stok_saat_ini,
                                }"
                                placeholder="0"
                            />
                            <small
                                v-if="form.errors.stok_saat_ini"
                                class="text-danger"
                            >
                                {{ form.errors.stok_saat_ini }}
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button
                                type="submit"
                                class="btn btn-warning"
                                :disabled="form.processing"
                            >
                                <span
                                    v-if="form.processing"
                                    class="spinner-border spinner-border-sm me-1"
                                ></span>
                                {{ form.processing ? "Menyimpan..." : "Update" }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="router.get(route('produk.index'))"
                            >
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
