<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $datas = [];
        $now = now();

        for ($i = 0; $i < 10000; $i++) {
            $datas[] = [
                'nama_supplier' => fake()->company().' - '.fake()->city(),
                'kontak' => fake()->phoneNumber(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($datas) === 1000) {
                DB::table('suppliers')->insert($datas);
                $datas = [];
            }
        }

        if (! empty($datas)) {
            DB::table('suppliers')->insert($datas);
        }
    }
}
