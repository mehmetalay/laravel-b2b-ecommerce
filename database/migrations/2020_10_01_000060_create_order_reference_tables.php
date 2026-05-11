<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 100)->nullable();
            $table->string('back_color_name', 100)->nullable();
            $table->string('front_color_name', 100)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });

        Schema::create('cargo_companies', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('is_default', false, false)->nullable()->default(0);
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('code', 50)->nullable();
            $table->string('symbol', 10)->nullable();
            $table->decimal('buy', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('sell', 15, 10, false)->nullable()->default('0.0000000000');
            $table->integer('manual_override', false, false)->nullable()->default(0);
            $table->decimal('manual_buy', 15, 6, false)->nullable()->default('0.000000');
            $table->decimal('manual_sell', 15, 6, false)->nullable()->default('0.000000');
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('payment_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->integer('row_id', false, false);
            $table->string('name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique('row_id', 'row_id');
        });

        Schema::create('payment_plans', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->integer('row_id', false, false);
            $table->string('code', 255)->nullable();
            $table->string('definition', 255)->nullable();
            $table->string('orglogicref', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique('row_id', 'row_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('cargo_companies');
        Schema::dropIfExists('order_statuses');
    }
};
