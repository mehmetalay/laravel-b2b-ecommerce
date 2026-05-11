<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE attributes MODIFY sort_order INT NULL DEFAULT 1');
            DB::statement('ALTER TABLE attribute_values MODIFY sort_order INT NULL DEFAULT 1');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE attributes MODIFY sort_order INT NULL DEFAULT NULL');
            DB::statement('ALTER TABLE attribute_values MODIFY sort_order INT NULL DEFAULT NULL');
        }
    }
};
