<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $produk = Produk::with('satuan')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_produk', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Produk/Index', [
            'produk' => $produk,
            'filters' => $request->only('search'),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        return response()->json(
            Produk::where('nama_produk', 'like', "%{$search}%")
                ->select('id', 'nama_produk', 'harga_beli_terakhir')
                ->take(20)
                ->get()
        );
    }

    public function create()
    {
        $satuans = Satuan::select('id', 'nama_satuan')->get();

        return Inertia::render('Produk/Create', ['satuans' => $satuans]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255', 'unique:produks,nama_produk'],
            'satuan_id' => ['required', 'exists:satuans,id'],
            'harga_beli_terakhir' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_beli_terakhir'],
            'stok_saat_ini' => ['required', 'integer', 'min:0'],
        ], [
            'harga_jual.gte' => 'Harga jual harus >= harga beli',
        ]);

        Produk::create($validated);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $produk = Produk::findOrFail($id);
        $satuans = Satuan::select('id', 'nama_satuan')->get();

        return Inertia::render('Produk/Edit', [
            'produk' => $produk,
            'satuans' => $satuans,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255', 'unique:produks,nama_produk,'.$id],
            'satuan_id' => ['required', 'exists:satuans,id'],
            'harga_beli_terakhir' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_beli_terakhir'],
            'stok_saat_ini' => ['required', 'integer', 'min:0'],
        ], [
            'harga_jual.gte' => 'Harga jual harus >= harga beli',
        ]);

        Produk::findOrFail($id)->update($validated);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diupdate');
    }

    public function destroy(string $id)
    {
        Produk::findOrFail($id)->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus');
    }
}
