<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionUsers = [
            [
                'id' => 10,
                'admin_id' => 1,
                'permission_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'admin_id' => 1,
                'permission_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'admin_id' => 1,
                'permission_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'admin_id' => 1,
                'permission_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'admin_id' => 1,
                'permission_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'admin_id' => 1,
                'permission_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'admin_id' => 1,
                'permission_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 1,
                'admin_id' => 1,
                'permission_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'admin_id' => 1,
                'permission_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'admin_id' => 1,
                'permission_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'admin_id' => 1,
                'permission_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'admin_id' => 1,
                'permission_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('permission_user')->upsert(
            $permissionUsers,
            [
                'id',
            ],
            [
                'admin_id',
                'permission_id',
                'updated_at',
            ]
        );
    }
}
