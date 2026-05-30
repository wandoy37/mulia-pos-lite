<?php

use App\Models\DetailPembelian;
use App\Models\Pembelian;
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
        'tanggal_pembelian' => '2026-05-28',
        'details' => [
            ['produk_id' => $this->produk->id, 'qty' => 5, 'harga_satuan' => 10000],
        ],
    ]);

    $response->assertRedirect(route('pembelian.index'));

    $this->assertDatabaseHas('pembelians', [
        'no_refrensi' => 'PB-00001',
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

    $response->assertSessionHasErrors(['supplier_id', 'tanggal_pembelian', 'details']);
});

test('validates details minimum one item', function () {
    $response = $this->post(route('pembelian.store'), [
        'supplier_id' => $this->supplier->id,
        'tanggal_pembelian' => '2026-05-28',
        'details' => [],
    ]);

    $response->assertSessionHasErrors(['details']);
});

test('can update pembelian', function () {
    $pembelian = Pembelian::factory()
        ->has(DetailPembelian::factory()->count(2), 'detailPembelian')
        ->create();
    $originalRef = $pembelian->no_refrensi;

    $response = $this->put(route('pembelian.update', $pembelian), [
        'supplier_id' => $this->supplier->id,
        'tanggal_pembelian' => '2026-05-29',
        'details' => [
            ['produk_id' => $this->produk->id, 'qty' => 10, 'harga_satuan' => 15000],
        ],
    ]);

    $response->assertRedirect(route('pembelian.index'));

    $this->assertDatabaseHas('pembelians', [
        'id' => $pembelian->id,
        'no_refrensi' => $originalRef,
        'total_harga' => 150000,
    ]);

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
