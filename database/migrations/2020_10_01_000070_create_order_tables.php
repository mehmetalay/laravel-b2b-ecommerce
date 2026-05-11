<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('user_id', false, false)->nullable();
            $table->bigInteger('plasiyer_id', false, false)->nullable();
            $table->bigInteger('sub_dealer_id', false, false)->nullable();
            $table->enum('creator_type', ['salesman', 'dealer', 'subdealer'])->nullable();
            $table->decimal('total_product_price', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('total_price', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('total_quantity', 20, 0, false)->nullable()->default('0');
            $table->decimal('unique_product_count', 20, 0, false)->nullable()->default('0');
            $table->integer('cart_discount_rate_1', false, false)->nullable()->default(0);
            $table->decimal('cart_discount_1', 20, 10, false)->nullable()->default('0.0000000000');
            $table->integer('cart_discount_rate_2', false, false)->nullable()->default(0);
            $table->decimal('cart_discount_2', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('total_discount_amount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->integer('payment_plan_id', false, false)->nullable();
            $table->integer('payment_type_id', false, false)->nullable();
            $table->text('explanation')->nullable();
            $table->string('currency', 50)->nullable();
            $table->bigInteger('campaign_id', false, false)->nullable();
            $table->string('campaign_type', 255)->nullable();
            $table->decimal('campaign_discount_total', 20, 10, false)->nullable()->default('0.0000000000');
            $table->tinyInteger('has_free_shipping', false, false)->nullable()->default(0);
            $table->longText('campaign_snapshot')->nullable();
            $table->tinyInteger('has_campaign', false, false)->nullable()->default(0);
            $table->text('order_note')->nullable();
            $table->text('ip_address')->nullable();
            $table->tinyInteger('email_sent', false, false)->nullable()->default(0);
            $table->enum('email_status', ['sent', 'not_sent_no_email_address'])->nullable();
            $table->tinyInteger('sms_sent', false, false)->nullable()->default(0);
            $table->enum('sms_status', ['sent', 'not_sent_no_phone'])->nullable();
            $table->tinyInteger('send_email', false, false)->nullable()->default(0);
            $table->tinyInteger('send_sms', false, false)->nullable()->default(0);
            $table->integer('order_status_id', false, false)->nullable()->default(1);
            $table->decimal('usd_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('eur_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('gbp_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->enum('status', ['pending', 'approved', 'cancelled'])->nullable()->default('approved');
            $table->bigInteger('approved_by_dealer', false, false)->nullable();
            $table->longText('note')->nullable();
            $table->enum('delivery_type', ['cargo', 'freight', 'warehouse', 'Kargo', 'Depo Teslim', 'Ambar', 'Transit Sevk'])->nullable();
            $table->integer('cargo_company_id', false, false)->nullable();
            $table->string('warehouse_name', 255)->nullable();
            $table->string('pickup_person', 255)->nullable();
            $table->text('transit_note')->nullable();
            $table->bigInteger('shipping_address_id', false, false)->nullable();
            $table->longText('shipping_address_snapshot')->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('total_price_excl_vat', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('total_vat_amount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('subtotal_before_discount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('subtotal_after_line_discount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('line_discount_total', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('grand_discount_total', 20, 10, false)->nullable()->default('0.0000000000');
            $table->string('erp_document_no', 255)->nullable();
            $table->enum('erp_status', ['pending', 'processing', 'sent', 'failed'])->nullable()->default('sent');
            $table->dateTime('erp_processing_at')->nullable();
            $table->dateTime('erp_synced_at')->nullable();
            $table->integer('erp_attempts', false, false)->nullable()->default(0);
            $table->longText('erp_last_error')->nullable();
            $table->dateTime('erp_last_failed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('order_products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('order_id', false, false)->nullable();
            $table->bigInteger('product_id', false, false)->nullable();
            $table->decimal('price', 20, 10, false)->nullable()->default('0.0000000000');
            $table->integer('quantity', false, false)->nullable();
            $table->decimal('discount', 10, 6, false)->nullable()->default('0.000000');
            $table->text('explanation')->nullable();
            $table->bigInteger('campaign_id', false, false)->nullable();
            $table->decimal('campaign_discount_percent', 20, 10, false)->default('0.0000000000');
            $table->tinyInteger('is_campaign_gift', false, false)->nullable()->default(0);
            $table->decimal('campaign_discount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->integer('campaign_total_quantity', false, false)->nullable();
            $table->integer('campaign_free_quantity', false, false)->nullable();
            $table->decimal('campaign_row_ratio', 20, 10, false)->nullable()->default('0.0000000000');
            $table->longText('campaign_context')->nullable();
            $table->text('campaign_note')->nullable();
            $table->decimal('unit_price', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('discount_rate', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('unit_price_after_discount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('vat_rate', 20, 10, false)->nullable()->default('0.0000000000');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');
    }
};
