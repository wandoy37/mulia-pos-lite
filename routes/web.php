<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
use App\Models\Pembelian;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/kopi', function () {
//     return view('kopi');
// });

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Route Satuan
Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index');
Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store');
Route::get('/satuan/{satuan}/edit', [SatuanController::class, 'edit'])->name('satuan.edit');
Route::put('/satuan/{satuan}', [SatuanController::class, 'update'])->name('satuan.update');
Route::delete('/satuan/{satuan}', [SatuanController::class, 'destroy'])->name('satuan.destroy');

// Route Supplier
Route::get('/supplier/search', [SupplierController::class, 'search'])->name('supplier.search');
Route::resource('supplier', SupplierController::class)->except(['create', 'edit', 'show']);

// Route Produk
Route::get('/produk/search', [ProdukController::class, 'search'])->name('produk.search');
Route::resource('produk', ProdukController::class)->except(['show']);

// Route Pembelian
Route::resource('pembelian', PembelianController::class);

Route::get('/pembelian/{pembelian}/detail', function (Pembelian $pembelian) {
    $pembelian->load('detailPembelian.produk');

    return response()->json([
        'details' => $pembelian->detailPembelian->map(fn ($d) => [
            'produk_id' => $d->produk_id,
            'nama_produk' => $d->produk->nama_produk,
            'harga_beli_terakhir' => $d->produk->harga_beli_terakhir,
            'qty' => $d->qty,
            'harga_satuan' => $d->harga_satuan,
        ]),
    ]);
})->name('pembelian.detail');
