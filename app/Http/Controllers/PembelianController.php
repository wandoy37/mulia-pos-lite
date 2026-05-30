<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PembelianController extends Controller
{
    private function getNextRefNumber(): string
    {
        $last = Pembelian::withTrashed()
            ->latest('id')
            ->value('no_refrensi');

        $number = $last ? (int) substr($last, 3) + 1 : 1;

        return 'PB-'.str_pad($number, 5, '0', STR_PAD_LEFT);
    }

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

        return Inertia::render('Pembelian/Index', [
            'pembelians' => $pembelians,
            'nextRef' => $this->getNextRefNumber(),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'tanggal_pembelian' => ['required', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.produk_id' => ['required', 'exists:produks,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        $totalHarga = collect($validated['details'])->sum(fn ($d) => $d['qty'] * $d['harga_satuan']);

        $pembelian = Pembelian::create([
            'supplier_id' => $validated['supplier_id'],
            'no_refrensi' => $this->getNextRefNumber(),
            'tanggal_pembelian' => $validated['tanggal_pembelian'],
            'total_harga' => $totalHarga,
        ]);

        foreach ($validated['details'] as $detail) {
            $pembelian->detailPembelian()->create($detail);
            Produk::where('id', $detail['produk_id'])->increment('stok_saat_ini', $detail['qty']);
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
            'tanggal_pembelian' => ['required', 'date'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.produk_id' => ['required', 'exists:produks,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        $totalHarga = collect($validated['details'])->sum(fn ($d) => $d['qty'] * $d['harga_satuan']);

        $pembelian->update([
            'supplier_id' => $validated['supplier_id'],
            'tanggal_pembelian' => $validated['tanggal_pembelian'],
            'total_harga' => $totalHarga,
        ]);

        $oldDetails = $pembelian->detailPembelian()->withTrashed()->get();
        foreach ($oldDetails as $oldDetail) {
            Produk::where('id', $oldDetail->produk_id)->decrement('stok_saat_ini', $oldDetail->qty);
        }

        $pembelian->detailPembelian()->delete();
        foreach ($validated['details'] as $detail) {
            $pembelian->detailPembelian()->create($detail);
            Produk::where('id', $detail['produk_id'])->increment('stok_saat_ini', $detail['qty']);
        }

        return redirect()->route('pembelian.index')
            ->with('success', 'Pembelian berhasil diperbarui');
    }

    public function destroy(Pembelian $pembelian)
    {
        foreach ($pembelian->detailPembelian as $detail) {
            Produk::where('id', $detail->produk_id)->decrement('stok_saat_ini', $detail->qty);
        }

        $pembelian->detailPembelian()->delete();
        $pembelian->delete();

        return redirect()->route('pembelian.index')
            ->with('success', 'Pembelian berhasil dihapus');
    }
}
