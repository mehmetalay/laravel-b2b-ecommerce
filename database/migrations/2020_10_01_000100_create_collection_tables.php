<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('plasiyer_id', false, false)->nullable();
            $table->bigInteger('sub_dealer_id', false, false)->nullable();
            $table->bigInteger('user_id', false, false)->nullable();
            $table->enum('creator_type', ['salesman', 'dealer', 'subdealer'])->nullable();
            $table->enum('type', ['Nakit', 'Çek', 'Senet'])->nullable();
            $table->string('sequence_number', 100)->nullable();
            $table->decimal('amount', 15, 2, false)->default('0.00');
            $table->string('currency_type', 50)->nullable();
            $table->integer('maturity_number', false, false)->nullable()->default(1);
            $table->dateTime('collection_date')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('collection_cheques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('collection_id', false, false)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('maturity_date')->nullable();
            $table->enum('clio_type', ['Kendisi', 'Müşteri'])->nullable();
            $table->string('debtor', 255)->nullable();
            $table->decimal('amount', 15, 2, false)->nullable()->default('0.00');
            $table->enum('currency_type', ['TL', 'USD', 'EUR'])->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('branch_code', 100)->nullable();
            $table->string('iban', 100)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('collection_promissories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('collection_id', false, false)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('maturity_date')->nullable();
            $table->enum('clio_type', ['Kendisi', 'Müşteri'])->nullable();
            $table->string('debtor', 255)->nullable();
            $table->decimal('amount', 15, 2, false)->nullable()->default('0.00');
            $table->enum('currency_type', ['TL', 'USD', 'EUR'])->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_promissories');
        Schema::dropIfExists('collection_cheques');
        Schema::dropIfExists('collections');
    }
};
