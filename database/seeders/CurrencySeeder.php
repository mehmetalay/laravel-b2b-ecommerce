<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'id' => 1,
                'code' => 'TL',
                'symbol' => '₺',
                'buy' => 1,
                'sell' => 1,
                'manual_override' => 0,
                'manual_buy' => 1,
                'manual_sell' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'USD',
                'symbol' => '$',
                'buy' => 45,
                'sell' => 45,
                'manual_override' => 0,
                'manual_buy' => 46,
                'manual_sell' => 46,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'code' => 'EUR',
                'symbol' => '€',
                'buy' => 55,
                'sell' => 55,
                'manual_override' => 0,
                'manual_buy' => 56,
                'manual_sell' => 56,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'code' => 'GBP',
                'symbol' => '£',
                'buy' => 60,
                'sell' => 60,
                'manual_override' => 0,
                'manual_buy' => 61,
                'manual_sell' => 61,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('currencies')->upsert(
            $currencies,
            [
                'id',
            ],
            [
                'code',
                'symbol',
                'buy',
                'sell',
                'payment_buy',
                'payment_sell',
                'manual_override',
                'manual_buy',
                'manual_sell',
                'status',
                'updated_at',
            ]
        );
    }
}
