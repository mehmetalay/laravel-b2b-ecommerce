<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->text('name')->nullable();
            $table->text('slug')->nullable();
            $table->string('image', 100)->nullable();
            $table->string('image_en', 100)->nullable();
            $table->integer('parent_id', false, false)->nullable();
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->integer('sort_order', false, false)->nullable();
            $table->integer('stock_display_limit', false, false)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
            $table->index('status', 'status');
            $table->index(['deleted_at', 'status'], 'categories_deleted_at_status_index');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 255)->nullable();
            $table->text('slug')->nullable();
            $table->string('image', 255)->nullable();
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->string('allowed_payment_methods', 255)->nullable()->default('cash,credit,term');
            $table->integer('sort_order', false, false)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->integer('category_id', false, false)->nullable();
            $table->bigInteger('brand_id', false, false)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('name_en', 255)->nullable();
            $table->string('code', 191);
            $table->string('code_2', 255)->nullable();
            $table->string('code_group', 255)->nullable();
            $table->decimal('price', 15, 2, false)->nullable()->default('0.00');
            $table->string('currency', 50)->nullable()->default('TL');
            $table->decimal('price_1', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_1_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_1_currency', 50)->nullable();
            $table->decimal('price_2', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_2_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_2_currency', 50)->nullable();
            $table->decimal('price_3', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_3_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_3_currency', 50)->nullable();
            $table->decimal('price_4', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_4_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_4_currency', 50)->nullable();
            $table->decimal('price_5', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_5_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_5_currency', 50)->nullable();
            $table->decimal('price_6', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_6_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_6_currency', 50)->nullable();
            $table->decimal('price_7', 25, 15, false)->nullable()->default('0.000000000000000');
            $table->decimal('price_7_discount_rate', 15, 2, false)->nullable()->default('0.00');
            $table->string('price_7_currency', 50)->nullable();
            $table->tinyInteger('is_special_currency', false, false)->nullable()->default(0);
            $table->decimal('special_currency_rate', 15, 2, false)->nullable()->default('0.00');
            $table->integer('vat_rate', false, false)->nullable()->default(20);
            $table->tinyInteger('is_vat_included', false, false)->nullable()->default(1);
            $table->integer('stock', false, false)->nullable()->default(0);
            $table->string('barcode', 255)->nullable();
            $table->tinyInteger('is_flagged_as_new', false, false)->nullable()->default(0);
            $table->string('image_1', 100)->nullable()->default('urun-gorseli-hazirlaniyor.jpg');
            $table->string('image_2', 100)->nullable();
            $table->string('image_3', 100)->nullable();
            $table->text('slug')->nullable();
            $table->integer('box_quantity', false, false)->nullable()->default(1);
            $table->tinyInteger('box_quantity_must_be_exact', false, false)->nullable()->default(0);
            $table->string('unit_name_1', 50)->nullable();
            $table->string('unit_name_2', 50)->nullable();
            $table->bigInteger('unit_quantity_2', false, false)->nullable();
            $table->string('unit_name_3', 50)->nullable();
            $table->bigInteger('unit_quantity_3', false, false)->nullable()->default(0);
            $table->string('unit_name_4', 50)->nullable();
            $table->bigInteger('unit_quantity_4', false, false)->nullable()->default(0);
            $table->tinyInteger('batch_update_processed', false, false)->nullable()->default(0);
            $table->tinyInteger('batch_price_update_processed', false, false)->nullable()->default(0);
            $table->tinyInteger('status', false, false)->default(1);
            $table->dateTime('erp_created_at')->nullable();
            $table->dateTime('erp_updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->index('category_id', 'category_id');
            $table->index('code', 'code');
            $table->unique('code', 'code_2');
            $table->index('code_group', 'code_group');
            $table->index('name', 'name');
            $table->index('name_en', 'name_en');
            $table->index('status', 'status');
            $table->index('stock', 'stock');
        });

        Schema::create('product_files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('product_id', false, false);
            $table->string('name', 255)->nullable();
            $table->enum('type', ['link', 'file'])->nullable()->default('file');
            $table->text('value')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_files');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
