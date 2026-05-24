<script setup>
import DashboardLayout from "../../Layouts/DashboardLayout.vue";
import BaseButton from "../../Components/UI/BaseButton.vue";
import Create from "./Create.vue";
import TabelSatuan from "./TabelSatuan.vue";

defineProps({
    satuans: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <DashboardLayout>
        <div class="row">
            <div class="col-lg-12">
                <h1>Satuan</h1>
                <p>Daftar satuan yang digunakan dalam sistem.</p>
            </div>
            <div class="col-lg-6 col-sm-12 mb-4">
                <Create />
            </div>
            <div class="col-lg-6 col-sm-12 mb-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Daftar Satuan</h5>
                        <TabelSatuan :satuans="satuans" />
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
