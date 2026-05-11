<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderStatuses = [
            [
                'id' => 1,
                'name' => 'Onay Bekleniyor',
                'back_color_name' => 'bg-light-secondary',
                'front_color_name' => 'alert-secondary',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Onaylandı',
                'back_color_name' => 'bg-light-success',
                'front_color_name' => 'alert-primary',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Hazırlanıyor',
                'back_color_name' => 'bg-info',
                'front_color_name' => 'alert-info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Sevk Edildi',
                'back_color_name' => 'bg-success',
                'front_color_name' => 'alert-success',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('order_statuses')->upsert(
            $orderStatuses,
            [
                'id',
            ],
            [
                'name',
                'back_color_name',
                'front_color_name',
                'updated_at',
            ]
        );
    }
}
