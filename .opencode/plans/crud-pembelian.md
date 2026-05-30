# Rencana Eksekusi CRUD Pembelian

---

## Step 1 — Update Model Pembelian

**File:** `app/Models/Pembelian.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['supplier_id', 'no_refrensi', 'tanggal_pembelian', 'total_harga'])]
class Pembelian extends Model
{
    use HasFactory, SoftDeletes;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class);
    }
}
```

> **Catatan:** `no_refrensi` (sesuai migrasi, bukan `no_referensi`)

---

## Step 2 — Update Model DetailPembelian

**File:** `app/Models/DetailPembelian.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['pembelian_id', 'produk_id', 'qty', 'harga_satuan'])]
class DetailPembelian extends Model
{
    use HasFactory, SoftDeletes;

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }
}
```

---

## Step 3 — Implementasi PembelianController

**File:** `app/Http/Controllers/PembelianController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $pembelians = Pembelian::with('supplier', 'detailPembelian.produk')
            ->when($request->search, function ($query, $search) {
                $query->where('no_refrensi', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('nama_supplier', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10);

        $suppliers = Supplier::select('id', 'nama_supplier')->get();
        $produks = Produk::select('id', 'nama_produk', 'harga_beli_terakhir')->get();

        return Inertia::render('Pembelian/Index', [
            'pembelians' => $pembelians,
            'suppliers' => $suppliers,
            'produks' => $produks,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'no_refrensi' => ['required', 'string', 'max:255', 'unique:pembelians,no_refrensi'],
            'tanggal_pembelian' => ['required', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.produk_id' => ['required', 'exists:produks,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        $totalHarga = collect($validated['details'])->sum(fn ($d) => $d['qty'] * $d['harga_satuan']);

        $pembelian = Pembelian::create([
            'supplier_id' => $validated['supplier_id'],
            'no_refrensi' => $validated['no_refrensi'],
            'tanggal_pembelian' => $validated['tanggal_pembelian'],
            'total_harga' => $totalHarga,
        ]);

        foreach ($validated['details'] as $detail) {
            $pembelian->detailPembelian()->create($detail);
        }

        return redirect()->route('pembelian.index')
            ->with('success', 'Pembelian berhasil ditambahkan');
    }

    public function show(Pembelian $pembelian)
    {
        $pembelian->load('supplier', 'detailPembelian.produk');

        return Inertia::render('Pembelian/Show', [
            'pembelian' => $pembelian,
        ]);
    }

    public function edit(Pembelian $pembelian)
    {
        $pembelian->load('detailPembelian');

        $suppliers = Supplier::select('id', 'nama_supplier')->get();
        $produks = Produk::select('id', 'nama_produk', 'harga_beli_terakhir')->get();

        return Inertia::render('Pembelian/Edit', [
            'pembelian' => $pembelian,
            'suppliers' => $suppliers,
            'produks' => $produks,
        ]);
    }

    public function update(Request $request, Pembelian $pembelian)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'no_refrensi' => ['required', 'string', 'max:255', 'unique:pembelians,no_refrensi,' . $pembelian->id],
            'tanggal_pembelian' => ['required', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.produk_id' => ['required', 'exists:produks,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        $totalHarga = collect($validated['details'])->sum(fn ($d) => $d['qty'] * $d['harga_satuan']);

        $pembelian->update([
            'supplier_id' => $validated['supplier_id'],
            'no_refrensi' => $validated['no_refrensi'],
            'tanggal_pembelian' => $validated['tanggal_pembelian'],
            'total_harga' => $totalHarga,
        ]);

        // Hapus detail lama, insert ulang
        $pembelian->detailPembelian()->delete();
        foreach ($validated['details'] as $detail) {
            $pembelian->detailPembelian()->create($detail);
        }

        return redirect()->route('pembelian.index')
            ->with('success', 'Pembelian berhasil diperbarui');
    }

    public function destroy(Pembelian $pembelian)
    {
        $pembelian->detailPembelian()->delete();
        $pembelian->delete();

        return redirect()->route('pembelian.index')
            ->with('success', 'Pembelian berhasil dihapus');
    }
}
```

---

## Step 4 — Rewrite Pembelian/Index.vue (Modal-based CRUD)

**File:** `resources/js/Pages/Pembelian/Index.vue`

```vue
<script setup>
import { inject, ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Modal } from "bootstrap";
import DashboardLayout from "../../Layouts/DashboardLayout.vue";

const route = inject("route");
const page = usePage();

const props = defineProps({
    pembelians: { type: Object, default: () => ({}) },
    suppliers: { type: Array, default: () => [] },
    produks: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const flash = computed(() => page.props.flash);
const search = ref(props.filters.search || "");

const form = useForm({
    supplier_id: "",
    no_refrensi: "",
    tanggal_pembelian: "",
    details: [{ produk_id: "", qty: 1, harga_satuan: 0 }],
});

const modalRef = ref(null);
const detailModalRef = ref(null);
const editingId = ref(null);
const viewingDetail = ref(null);
let bsModal = null;
let bsDetailModal = null;

let debounceTimer = null;

const isEdit = computed(() => editingId.value !== null);
const modalTitle = computed(() =>
    isEdit.value ? "Edit Pembelian" : "Tambah Pembelian",
);

const totalHarga = computed(() =>
    form.details.reduce((sum, d) => sum + (Number(d.qty) || 0) * (Number(d.harga_satuan) || 0), 0),
);

const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const openCreateModal = () => {
    editingId.value = null;
    form.reset();
    form.details = [{ produk_id: "", qty: 1, harga_satuan: 0 }];
    form.clearErrors();
    bsModal.show();
};

const openEditModal = async (pembelian) => {
    editingId.value = pembelian.id;
    form.clearErrors();
    form.supplier_id = pembelian.supplier_id;
    form.no_refrensi = pembelian.no_refrensi;
    form.tanggal_pembelian = pembelian.tanggal_pembelian;

    // Fetch detail items
    try {
        const response = await fetch(`/pembelian/${pembelian.id}/detail`);
        const data = await response.json();
        form.details = data.details.map((d) => ({
            produk_id: d.produk_id,
            qty: d.qty,
            harga_satuan: d.harga_satuan,
        }));
    } catch {
        form.details = [{ produk_id: "", qty: 1, harga_satuan: 0 }];
    }

    bsModal.show();
};

const openDetailModal = async (pembelian) => {
    viewingDetail.value = pembelian;
    bsDetailModal.show();
};

const closeModal = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    bsModal.hide();
};

const addDetailRow = () => {
    form.details.push({ produk_id: "", qty: 1, harga_satuan: 0 });
};

const removeDetailRow = (index) => {
    if (form.details.length > 1) {
        form.details.splice(index, 1);
    }
};

const submit = () => {
    if (editingId.value) {
        form.put(route("pembelian.update", editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("pembelian.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const hapus = (pembelian) => {
    if (confirm("Apakah Anda yakin ingin menghapus pembelian ini?")) {
        router.delete(route("pembelian.destroy", pembelian.id));
    }
};

const goToPage = (link) => {
    if (!link.url || link.active) return;
    const url = new URL(link.url);
    const pageParam = url.searchParams.get("page");
    router.get(
        route("pembelian.index"),
        { search: search.value, page: pageParam },
        { preserveState: true, preserveScroll: true },
    );
};

watch(search, (value) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route("pembelian.index"),
            { search: value },
            { preserveState: true, replace: true },
        );
    }, 300);
});

onMounted(() => {
    if (modalRef.value) {
        bsModal = new Modal(modalRef.value, { backdrop: "static" });
    }
    if (detailModalRef.value) {
        bsDetailModal = new Modal(detailModalRef.value, { backdrop: "static" });
    }
});

onBeforeUnmount(() => {
    if (bsModal) {
        bsModal.dispose();
        bsModal = null;
    }
    if (bsDetailModal) {
        bsDetailModal.dispose();
        bsDetailModal = null;
    }
});

// Get product harga_beli_terakhir when produk dipilih
const onProdukChange = (index) => {
    const selected = props.produks.find(
        (p) => p.id == form.details[index].produk_id,
    );
    if (selected) {
        form.details[index].harga_satuan = Number(selected.harga_beli_terakhir);
    }
};
</script>

<template>
    <DashboardLayout>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Pembelian</h1>
                <p class="text-muted mb-0">
                    Daftar transaksi pembelian barang.
                </p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">
                + Tambah Pembelian
            </button>
        </div>

        <div
            v-if="flash.success"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            {{ flash.success }}
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>

        <div class="card shadow">
            <div class="card-body">
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
                                placeholder="Cari no. refrensi atau supplier..."
                            />
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px">No</th>
                                <th>No. Refrensi</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Total Harga</th>
                                <th style="width: 220px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-if="
                                    !pembelians.data ||
                                    pembelians.data.length === 0
                                "
                            >
                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4"
                                >
                                    Belum ada data pembelian
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in pembelians.data"
                                :key="item.id"
                            >
                                <th scope="row">
                                    {{ pembelians.from + index }}
                                </th>
                                <td>{{ item.no_refrensi }}</td>
                                <td>{{ item.tanggal_pembelian }}</td>
                                <td>{{ item.supplier?.nama_supplier ?? "-" }}</td>
                                <td>{{ formatRupiah(item.total_harga) }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button
                                            class="btn btn-info btn-sm text-white"
                                            @click="openDetailModal(item)"
                                        >
                                            Detail
                                        </button>
                                        <button
                                            class="btn btn-warning btn-sm"
                                            @click="openEditModal(item)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            class="btn btn-danger btn-sm"
                                            @click="hapus(item)"
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
                    v-if="pembelians.data && pembelians.data.length > 0"
                    class="d-flex justify-content-between align-items-center mt-3"
                >
                    <small class="text-muted">
                        Menampilkan {{ pembelians.from }} -{{
                            pembelians.to
                        }}
                        dari {{ pembelians.total }} data
                    </small>
                    <nav v-if="pembelians.last_page > 1">
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                v-for="(link, i) in pembelians.links"
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
        </div>

        <!-- Modal Tambah/Edit Pembelian -->
        <div ref="modalRef" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ modalTitle }}</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeModal"
                        ></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <!-- Header Fields -->
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">
                                        No. Refrensi
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.no_refrensi"
                                        type="text"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.no_refrensi,
                                        }"
                                        placeholder="Otomatis"
                                    />
                                    <small
                                        v-if="form.errors.no_refrensi"
                                        class="text-danger"
                                    >
                                        {{ form.errors.no_refrensi }}
                                    </small>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label">
                                        Tanggal
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        v-model="form.tanggal_pembelian"
                                        type="date"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                form.errors.tanggal_pembelian,
                                        }"
                                    />
                                    <small
                                        v-if="form.errors.tanggal_pembelian"
                                        class="text-danger"
                                    >
                                        {{ form.errors.tanggal_pembelian }}
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Supplier
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        v-model="form.supplier_id"
                                        class="form-select"
                                        :class="{
                                            'is-invalid': form.errors.supplier_id,
                                        }"
                                    >
                                        <option value="" disabled>
                                            Pilih supplier
                                        </option>
                                        <option
                                            v-for="s in suppliers"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.nama_supplier }}
                                        </option>
                                    </select>
                                    <small
                                        v-if="form.errors.supplier_id"
                                        class="text-danger"
                                    >
                                        {{ form.errors.supplier_id }}
                                    </small>
                                </div>
                            </div>

                            <hr />

                            <!-- Detail Items -->
                            <div
                                class="d-flex justify-content-between align-items-center mb-2"
                            >
                                <label class="form-label fw-bold mb-0">
                                    Detail Item
                                </label>
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    @click="addDetailRow"
                                >
                                    + Tambah Item
                                </button>
                            </div>

                            <div
                                v-if="form.errors['details.0']"
                                class="text-danger small mb-2"
                            >
                                {{ form.errors["details.0"] }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 35%">Produk</th>
                                            <th style="width: 15%">Qty</th>
                                            <th style="width: 25%">
                                                Harga Satuan
                                            </th>
                                            <th style="width: 20%">Subtotal</th>
                                            <th style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(detail, i) in form.details"
                                            :key="i"
                                        >
                                            <td>
                                                <select
                                                    v-model="detail.produk_id"
                                                    class="form-select form-select-sm"
                                                    :class="{
                                                        'is-invalid':
                                                            form.errors[
                                                                `details.${i}.produk_id`
                                                            ],
                                                    }"
                                                    @change="onProdukChange(i)"
                                                >
                                                    <option value="" disabled>
                                                        Pilih produk
                                                    </option>
                                                    <option
                                                        v-for="p in produks"
                                                        :key="p.id"
                                                        :value="p.id"
                                                    >
                                                        {{ p.nama_produk }}
                                                    </option>
                                                </select>
                                                <small
                                                    v-if="
                                                        form.errors[
                                                            `details.${i}.produk_id`
                                                        ]
                                                    "
                                                    class="text-danger"
                                                >
                                                    {{
                                                        form.errors[
                                                            `details.${i}.produk_id`
                                                        ]
                                                    }}
                                                </small>
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="detail.qty"
                                                    type="number"
                                                    min="1"
                                                    class="form-control form-control-sm"
                                                    :class="{
                                                        'is-invalid':
                                                            form.errors[
                                                                `details.${i}.qty`
                                                            ],
                                                    }"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="
                                                        detail.harga_satuan
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="100"
                                                    class="form-control form-control-sm"
                                                    :class="{
                                                        'is-invalid':
                                                            form.errors[
                                                                `details.${i}.harga_satuan`
                                                            ],
                                                    }"
                                                />
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatRupiah(
                                                        (Number(detail.qty) ||
                                                            0) *
                                                            (Number(
                                                                detail.harga_satuan,
                                                            ) || 0),
                                                    )
                                                }}
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    @click="removeDetailRow(i)"
                                                    :disabled="
                                                        form.details.length <= 1
                                                    "
                                                >
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="text-end fw-bold"
                                            >
                                                Total Harga
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ formatRupiah(totalHarga) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="closeModal"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                <span
                                    v-if="form.processing"
                                    class="spinner-border spinner-border-sm me-1"
                                ></span>
                                {{
                                    form.processing ? "Menyimpan..." : "Simpan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Detail Pembelian -->
        <div ref="detailModalRef" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pembelian</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <div class="modal-body" v-if="viewingDetail">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted">No. Refrensi</small>
                                <p class="fw-bold mb-0">
                                    {{ viewingDetail.no_refrensi }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Tanggal</small>
                                <p class="fw-bold mb-0">
                                    {{ viewingDetail.tanggal_pembelian }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Supplier</small>
                                <p class="fw-bold mb-0">
                                    {{
                                        viewingDetail.supplier
                                            ?.nama_supplier ?? "-"
                                    }}
                                </p>
                            </div>
                        </div>
                        <hr />
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="d in viewingDetail.detail_pembelian"
                                    :key="d.id"
                                >
                                    <td>
                                        {{ d.produk?.nama_produk ?? "-" }}
                                    </td>
                                    <td>{{ d.qty }}</td>
                                    <td>{{ formatRupiah(d.harga_satuan) }}</td>
                                    <td class="text-end">
                                        {{
                                            formatRupiah(
                                                d.qty * d.harga_satuan,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td
                                        colspan="3"
                                        class="text-end fw-bold"
                                    >
                                        Total Harga
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{
                                            formatRupiah(
                                                viewingDetail.total_harga,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
```

---

## Step 5 — Update Navbar

**File:** `resources/js/Components/Layout/Navbar.vue`

**Pada bagian menu Pembelian (baris ~91-93), ganti:**
```vue
<li class="nav-item mx-4">
    <a class="nav-link" href="#">Pembelian</a>
</li>
```

**Menjadi:**
```vue
<li class="nav-item mx-4">
    <Link
        class="nav-link"
        :class="{ active: isActive('pembelian.*') }"
        :href="route('pembelian.index')"
    >
        Pembelian
    </Link>
</li>
```

**Dan tambahkan `pembelian.*` ke fungsi `isDataMasterActive()` jika ingin masuk grup Data Master (opsional), atau biarkan sebagai menu terpisah.**

---

## Step 6 — Tambah Route untuk Fetch Detail (via AJAX)

**File:** `routes/web.php`

Tambahkan route ini **setelah** resource pembelian:

```php
Route::get('/pembelian/{pembelian}/detail', function (App\Models\Pembelian $pembelian) {
    $pembelian->load('detailPembelian.produk');
    return response()->json([
        'details' => $pembelian->detailPembelian->map(fn ($d) => [
            'produk_id' => $d->produk_id,
            'qty' => $d->qty,
            'harga_satuan' => $d->harga_satuan,
        ]),
    ]);
})->name('pembelian.detail');
```

> **Catatan:** Route ini digunakan oleh modal edit untuk fetch detail items via AJAX `fetch()`.

---

## Step 7 — Regenerate Ziggy

Jalankan perintah:
```bash
php artisan ziggy:generate resources/js/ziggy.js
```

---

## Step 8 — Buat Factories

### `database/factories/PembelianFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PembelianFactory extends Factory
{
    protected $model = Pembelian::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'no_refrensi' => 'PO-' . strtoupper(fake()->bothify('####??')),
            'tanggal_pembelian' => fake()->date(),
            'total_harga' => 0,
        ];
    }
}
```

### `database/factories/DetailPembelianFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPembelianFactory extends Factory
{
    protected $model = DetailPembelian::class;

    public function definition(): array
    {
        return [
            'pembelian_id' => Pembelian::factory(),
            'produk_id' => Produk::factory(),
            'qty' => fake()->numberBetween(1, 100),
            'harga_satuan' => fake()->numberBetween(1000, 100000),
        ];
    }
}
```

---

## Step 9 — Buat Feature Test

**File:** `tests/Feature/PembelianTest.php`

Jalankan:
```bash
php artisan make:test --pest PembelianTest
```

Kemudian isi dengan:

```php
<?php

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->supplier = Supplier::factory()->create();
    $this->produk = Produk::factory()->create();
});

test('can list pembelian', function () {
    Pembelian::factory()
        ->has(DetailPembelian::factory()->count(2), 'detailPembelian')
        ->create();

    $response = $this->get(route('pembelian.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Pembelian/Index')
        ->has('pembelians.data', 1)
    );
});

test('can create pembelian with details', function () {
    $response = $this->post(route('pembelian.store'), [
        'supplier_id' => $this->supplier->id,
        'no_refrensi' => 'PO-TEST-001',
        'tanggal_pembelian' => '2026-05-28',
        'details' => [
            ['produk_id' => $this->produk->id, 'qty' => 5, 'harga_satuan' => 10000],
        ],
    ]);

    $response->assertRedirect(route('pembelian.index'));

    $this->assertDatabaseHas('pembelians', [
        'no_refrensi' => 'PO-TEST-001',
        'total_harga' => 50000,
    ]);

    $this->assertDatabaseHas('detail_pembelians', [
        'produk_id' => $this->produk->id,
        'qty' => 5,
        'harga_satuan' => 10000,
    ]);
});

test('validates required fields', function () {
    $response = $this->post(route('pembelian.store'), []);

    $response->assertSessionHasErrors(['supplier_id', 'no_refrensi', 'tanggal_pembelian', 'details']);
});

test('validates details minimum one item', function () {
    $response = $this->post(route('pembelian.store'), [
        'supplier_id' => $this->supplier->id,
        'no_refrensi' => 'PO-TEST-002',
        'tanggal_pembelian' => '2026-05-28',
        'details' => [],
    ]);

    $response->assertSessionHasErrors(['details']);
});

test('can update pembelian', function () {
    $pembelian = Pembelian::factory()
        ->has(DetailPembelian::factory()->count(2), 'detailPembelian')
        ->create();

    $response = $this->put(route('pembelian.update', $pembelian), [
        'supplier_id' => $this->supplier->id,
        'no_refrensi' => 'PO-UPDATED',
        'tanggal_pembelian' => '2026-05-29',
        'details' => [
            ['produk_id' => $this->produk->id, 'qty' => 10, 'harga_satuan' => 15000],
        ],
    ]);

    $response->assertRedirect(route('pembelian.index'));

    $this->assertDatabaseHas('pembelians', [
        'id' => $pembelian->id,
        'no_refrensi' => 'PO-UPDATED',
        'total_harga' => 150000,
    ]);

    // Detail lama harusnya sudah terhapus (soft delete)
    $this->assertSoftDeleted('detail_pembelians', [
        'pembelian_id' => $pembelian->id,
    ]);
});

test('can delete pembelian', function () {
    $pembelian = Pembelian::factory()
        ->has(DetailPembelian::factory()->count(2), 'detailPembelian')
        ->create();

    $response = $this->delete(route('pembelian.destroy', $pembelian));

    $response->assertRedirect(route('pembelian.index'));
    $this->assertSoftDeleted($pembelian);
});
```

> **Catatan:** Sebelum menjalankan test, pastikan `RefreshDatabase` di `tests/Pest.php` tidak dikomentari, atau tambahkan `uses(RefreshDatabase::class)` di dalam file test seperti di atas.

---

## Step 10 — Run Pint

```bash
./vendor/bin/pint --format agent
```

---

## Step 11 — Run Tests

```bash
composer test
```

Atau spesifik:
```bash
php artisan test --compact --filter=PembelianTest
```

---

## Ringkasan File yang Diubah/Dibuat

| # | File | Aksi |
|---|------|------|
| 1 | `app/Models/Pembelian.php` | **Edit** — tambah SoftDeletes, HasFactory, relationships |
| 2 | `app/Models/DetailPembelian.php` | **Edit** — tambah SoftDeletes, HasFactory, relationships |
| 3 | `app/Http/Controllers/PembelianController.php` | **Edit** — implementasi semua method |
| 4 | `resources/js/Pages/Pembelian/Index.vue` | **Edit** — rewrite total |
| 5 | `resources/js/Components/Layout/Navbar.vue` | **Edit** — ganti link Pembelian |
| 6 | `routes/web.php` | **Edit** — tambah route `pembelian.detail` |
| 7 | `resources/js/ziggy.js` | **Regenerate** |
| 8 | `database/factories/PembelianFactory.php` | **Buat baru** |
| 9 | `database/factories/DetailPembelianFactory.php` | **Buat baru** |
| 10 | `tests/Feature/PembelianTest.php` | **Buat baru** |
