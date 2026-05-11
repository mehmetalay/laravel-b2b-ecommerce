<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backed_up_carts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 255)->nullable();
            $table->bigInteger('user_id', false, false)->nullable();
            $table->integer('plasiyer_id', false, false)->nullable();
            $table->integer('sub_dealer_id', false, false)->nullable();
            $table->integer('cart_discount_rate_tl_1', false, false)->nullable();
            $table->integer('cart_discount_rate_tl_2', false, false)->nullable();
            $table->integer('cart_discount_rate_usd_1', false, false)->nullable();
            $table->integer('cart_discount_rate_usd_2', false, false)->nullable();
            $table->integer('cart_discount_rate_eur_1', false, false)->nullable();
            $table->integer('cart_discount_rate_eur_2', false, false)->nullable();
            $table->integer('cart_discount_rate_gbp_1', false, false)->nullable();
            $table->integer('cart_discount_rate_gbp_2', false, false)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('user_id', false, false)->nullable();
            $table->bigInteger('plasiyer_id', false, false)->nullable();
            $table->bigInteger('sub_dealer_id', false, false)->nullable();
            $table->bigInteger('product_id', false, false)->nullable();
            $table->integer('quantity', false, false)->nullable();
            $table->decimal('discount', 15, 6, false)->nullable()->default('0.000000');
            $table->text('explanation')->nullable();
            $table->tinyInteger('ordered', false, false)->nullable()->default(0);
            $table->tinyInteger('backed_up', false, false)->nullable()->default(0);
            $table->integer('backed_up_cart_id', false, false)->nullable();
            $table->string('currency', 50)->nullable();
            $table->string('exchange_type', 100)->nullable();
            $table->tinyInteger('order_separately', false, false)->nullable()->default(0);
            $table->string('payment_type', 20)->nullable()->default('cash');
            $table->bigInteger('campaign_id', false, false)->nullable();
            $table->bigInteger('trigger_cart_id', false, false)->nullable();
            $table->tinyInteger('is_campaign_gift', false, false)->nullable()->default(0);
            $table->string('campaign_rule_type', 255)->nullable();
            $table->tinyInteger('is_manual_override', false, false)->nullable()->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
        Schema::dropIfExists('backed_up_carts');
    }
};
