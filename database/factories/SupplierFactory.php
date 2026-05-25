<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'nama_supplier' => fake()->company().' - '.fake()->city(),
            'kontak' => fake()->phoneNumber(),
        ];
    }
}
