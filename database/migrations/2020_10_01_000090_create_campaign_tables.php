<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 255)->nullable();
            $table->text('general_description')->nullable();
            $table->enum('type', ['product', 'brand', 'category', 'cart'])->nullable()->default('product');
            $table->string('sub_type', 255)->nullable();
            $table->tinyInteger('status', false, false)->nullable()->default(0);
            $table->tinyInteger('use_date_filter', false, false)->nullable()->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('auto_apply', false, false)->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('campaign_rules', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('campaign_id', false, false);
            $table->string('rule_type', 100)->nullable();
            $table->longText('extra')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('campaign_products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->integer('campaign_id', false, false)->nullable();
            $table->bigInteger('product_id', false, false)->nullable();
            $table->bigInteger('brand_id', false, false)->nullable();
            $table->bigInteger('category_id', false, false)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('campaign_rules');
        Schema::dropIfExists('campaigns');
    }
};
