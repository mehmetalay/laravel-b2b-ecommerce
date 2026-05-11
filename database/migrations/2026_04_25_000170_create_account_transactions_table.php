<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sub_dealer_id')->nullable();
            $table->unsignedBigInteger('plasiyer_id')->nullable();
            $table->unsignedBigInteger('current_account_id')->nullable();
            $table->string('type', 50);
            $table->string('direction', 20);
            $table->decimal('amount', 18, 4)->default(0);
            $table->string('currency', 10)->nullable();
            $table->dateTime('transaction_date');
            $table->dateTime('due_date')->nullable();
            $table->text('description')->nullable();
            $table->string('reference_type', 255)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('status', 50)->default('approved');
            $table->unsignedBigInteger('reversal_of_id')->nullable();
            $table->string('source_key', 190)->unique();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['current_account_id', 'transaction_date'], 'acc_tx_current_account_date_idx');
            $table->index(['user_id', 'transaction_date'], 'acc_tx_user_date_idx');
            $table->index(['sub_dealer_id', 'transaction_date'], 'acc_tx_subdealer_date_idx');
            $table->index(['plasiyer_id', 'transaction_date'], 'acc_tx_plasiyer_date_idx');
            $table->index(['status', 'transaction_date'], 'acc_tx_status_date_idx');
            $table->index(['type', 'transaction_date'], 'acc_tx_type_date_idx');
            $table->index(['reference_type', 'reference_id'], 'acc_tx_reference_idx');
            $table->index(['deleted_at', 'status'], 'acc_tx_deleted_status_idx');
            $table->index('reversal_of_id', 'acc_tx_reversal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
