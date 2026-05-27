<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
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
Route::resource('supplier', SupplierController::class)->except(['create', 'edit', 'show']);

// Route Produk
Route::resource('produk', ProdukController::class)->except(['show']);
