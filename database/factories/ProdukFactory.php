<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdukFactory extends Factory
{
    protected $model = Produk::class;

    public function definition(): array
    {
        return [
            'satuan_id' => Satuan::factory(),
            'nama_produk' => fake()->word(),
            'harga_beli_terakhir' => fake()->numberBetween(1000, 50000),
            'harga_jual' => fake()->numberBetween(5000, 100000),
            'stok_saat_ini' => fake()->numberBetween(0, 100),
        ];
    }
}
