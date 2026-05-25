<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::select(['id', 'nama_supplier', 'kontak'])
            ->when($request->search, function ($query, $search) {
                $query->where('nama_supplier', 'like', "%{$search}%")
                    ->orWhere('kontak', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'min:1', 'unique:suppliers,nama_supplier'],
            'kontak' => ['nullable'],
        ], [
            'nama_supplier.unique' => 'Supplier sudah tersedia',
        ]);

        $validated['kontak'] = $validated['kontak'] ?: null;

        Supplier::create($validated);

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'min:1', 'unique:suppliers,nama_supplier,'.$supplier->id],
            'kontak' => ['nullable'],
        ], [
            'nama_supplier.unique' => 'Supplier sudah tersedia',
        ]);

        $validated['kontak'] = $validated['kontak'] ?: null;

        $supplier->update($validated);

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Supplier berhasil diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Supplier berhasil dihapus');
    }
}
