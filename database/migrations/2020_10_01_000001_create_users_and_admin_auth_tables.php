<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->bigInteger('current_account_id', false, false);
            $table->string('name', 255);
            $table->string('code', 100)->nullable();
            $table->string('group_code', 255)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('address_1')->nullable();
            $table->text('address_2')->nullable();
            $table->text('address_3')->nullable();
            $table->string('postal_code', 100)->nullable();
            $table->string('tax_office', 100)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('identity_number', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('dealer_code', 255)->nullable();
            $table->string('username', 255)->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->decimal('balance', 15, 2, false)->nullable()->default('0.00');
            $table->decimal('currency_balance', 15, 2, false)->nullable()->default('0.00');
            $table->string('currency', 10)->nullable()->default('TL');
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->tinyInteger('block_entry', false, false)->nullable()->default(0);
            $table->dateTime('last_login_date')->nullable();
            $table->text('last_login_ip')->nullable();
            $table->dateTime('admin_last_login_date')->nullable();
            $table->string('hide_category_ids', 100)->nullable();
            $table->string('hidden_product_prefixes', 255)->nullable();
            $table->tinyInteger('hide_all_prices', false, false)->nullable()->default(0);
            $table->tinyInteger('show_retail_price', false, false)->nullable()->default(0);
            $table->tinyInteger('hide_all_stock_quantities', false, false)->nullable()->default(0);
            $table->tinyInteger('group_by_product_code', false, false)->nullable()->default(0);
            $table->tinyInteger('report_access', false, false)->nullable()->default(1);
            $table->integer('min_stock_quantity', false, false)->nullable()->default(1);
            $table->string('plasiyer1', 50)->nullable();
            $table->string('plasiyer2', 50)->nullable();
            $table->string('plasiyer3', 50)->nullable();
            $table->string('plasiyer4', 50)->nullable();
            $table->string('plasiyer5', 50)->nullable();
            $table->decimal('plasiyer1_discount1', 15, 2, false)->nullable()->default('0.00');
            $table->decimal('plasiyer1_discount2', 15, 2, false)->nullable()->default('0.00');
            $table->decimal('plasiyer2_discount1', 15, 2, false)->nullable()->default('0.00');
            $table->decimal('plasiyer2_discount2', 15, 2, false)->nullable()->default('0.00');
            $table->integer('payment_reference', false, false)->nullable();
            $table->integer('payment_type', false, false)->nullable();
            $table->tinyInteger('increase_and_decrease_type', false, false)->nullable()->default(1);
            $table->integer('increase_and_decrease_rate', false, false)->nullable()->default(0);
            $table->integer('price_type', false, false)->nullable()->default(1);
            $table->tinyInteger('is_order_closed', false, false)->nullable()->default(0);
            $table->text('closure_reason')->nullable();
            $table->tinyInteger('password_must_change', false, false)->nullable()->default(1);
            $table->string('password_reset_code', 255)->nullable();
            $table->dateTime('password_reset_expires_at')->nullable();
            $table->enum('access_type', ['all_customers', 'specific_code'])->nullable();
            $table->tinyInteger('receipt_enabled', false, false)->nullable()->default(1);
            $table->tinyInteger('show_all_installments', false, false)->nullable()->default(0);
            $table->tinyInteger('can_edit_price', false, false)->default(0);
            $table->tinyInteger('can_edit_discount', false, false)->default(1);
            $table->integer('company_id', false, false)->nullable()->default(1);
            $table->tinyInteger('can_collect_payments', false, false)->nullable()->default(1);
            $table->enum('role', ['salesman', 'dealer'])->nullable()->default('dealer');
            $table->tinyInteger('is_installment_allowed', false, false)->nullable()->default(1);
            $table->integer('max_installment', false, false)->nullable()->default(12);
            $table->set('allowed_payment_methods', ['cash', 'credit', 'term'])->nullable()->default('cash,credit,term');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
            $table->unique('current_account_id', 'current_account_id');
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 255)->nullable();
            $table->string('surname', 255)->nullable();
            $table->string('username', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->integer('status', false, false)->nullable();
            $table->tinyInteger('block_entry', false, false)->nullable()->default(0);
            $table->string('remember_token', 255)->nullable();
            $table->string('last_login_ip', 255)->nullable();
            $table->dateTime('last_login_date')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->bigInteger('admin_id', false, true);
            $table->bigInteger('permission_id', false, true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique(['admin_id', 'permission_id'], 'admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('users');
    }
};
