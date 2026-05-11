<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntityLastUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entityLastUpdates = [
            [
                'id' => 1,
                'entity_type' => 'customer',
                'last_update_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'entity_type' => 'product',
                'last_update_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'entity_type' => 'image',
                'last_update_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('entity_last_updates')->upsert(
            $entityLastUpdates,
            [
                'id',
            ],
            [
                'entity_type',
                'last_update_date',
                'updated_at',
            ]
        );
    }
}
