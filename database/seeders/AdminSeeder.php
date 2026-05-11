<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'id' => 1,
                'name' => 'Admin',
                'surname' => 'Kullanıcısı',
                'username' => 'admin',
                'email' => 'admin@siteadi.com',
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'change-me')),
                'status' => 1,
                'block_entry' => 0,
                'remember_token' => null,
                'last_login_ip' => null,
                'last_login_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        DB::table('admins')->upsert(
            $admins,
            [
                'id',
            ],
            [
                'name',
                'surname',
                'username',
                'email',
                'password',
                'status',
                'block_entry',
                'remember_token',
                'last_login_ip',
                'last_login_date',
                'updated_at',
                'deleted_at',
            ]
        );
    }
}
