<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'id' => 1,
                'name' => 'TEST FIRMA',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        DB::table('companies')->upsert(
            $companies,
            [
                'id',
            ],
            [
                'name',
                'status',
                'updated_at',
                'deleted_at',
            ]
        );
    }
}
