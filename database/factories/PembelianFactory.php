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
            'no_refrensi' => 'PO-'.strtoupper(fake()->bothify('####??')),
            'tanggal_pembelian' => fake()->date(),
            'total_harga' => 0,
        ];
    }
}
