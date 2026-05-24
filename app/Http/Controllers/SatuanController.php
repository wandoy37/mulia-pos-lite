<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SatuanController extends Controller
{
    public function index()
    {
        $satuans = Satuan::latest()->get();

        return Inertia::render('Satuan/Index', [
            'satuans' => $satuans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan' => ['required', 'min:1'],
        ]);

        Satuan::create($validated);

        return redirect()
            ->route('satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan');
    }

    public function edit(Satuan $satuan)
    {
        return Inertia::render('Satuan/Edit', [
            'satuan' => $satuan,
        ]);
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validated = $request->validate([
            'nama_satuan' => ['required', 'min:1'],
        ]);

        $satuan->update($validated);

        return redirect()
            ->route('satuan.index')
            ->with('success', 'Satuan berhasil diperbarui');
    }

    public function destroy(Satuan $satuan)
    {
        $satuan->delete();

        return redirect()
            ->route('satuan.index')
            ->with('success', 'Satuan berhasil dihapus');
    }
}
