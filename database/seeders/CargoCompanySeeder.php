<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cargoCompanies = [
            [
                'id' => 1,
                'name' => 'TEST KARGO',
                'description' => null,
                'is_default' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        DB::table('cargo_companies')->upsert(
            $cargoCompanies,
            [
                'id',
            ],
            [
                'name',
                'description',
                'is_default',
                'status',
                'updated_at',
                'deleted_at',
            ]
        );
    }
}
