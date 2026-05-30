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
