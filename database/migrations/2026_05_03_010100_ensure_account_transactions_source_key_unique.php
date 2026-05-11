<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $indexName = 'account_transactions_source_key_unique_faz5';

    public function up(): void
    {
        if (!$this->tableExists('account_transactions')) {
            return;
        }

        if ($this->hasAnyUniqueIndexOnSourceKey()) {
            return;
        }

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->unique('source_key', $this->indexName);
        });
    }

    public function down(): void
    {
        if (!$this->tableExists('account_transactions')) {
            return;
        }

        if (!$this->hasIndexByName($this->indexName)) {
            return;
        }

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropUnique($this->indexName);
        });
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function hasAnyUniqueIndexOnSourceKey(): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select(
                "SHOW INDEX FROM account_transactions WHERE Column_name = 'source_key' AND Non_unique = 0"
            );

            return count($rows) > 0;
        }

        return false;
    }

    private function hasIndexByName(string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select(
                "SHOW INDEX FROM account_transactions WHERE Key_name = ?",
                [$indexName]
            );

            return count($rows) > 0;
        }

        return false;
    }
};
