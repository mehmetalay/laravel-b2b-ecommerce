<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('version', 100)->nullable();
            $table->enum('dealer_type', ['all', 'dealer', 'subdealer'])->nullable();
            $table->tinyInteger('is_active', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('customer_invoice_title', 255)->nullable();
            $table->text('customer_invoice_address')->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('fax', 100)->nullable();
            $table->string('trade_registry_no', 255)->nullable();
            $table->string('tax_office', 255)->nullable();
            $table->string('tax_number', 255)->nullable();
            $table->string('company_official', 255)->nullable();
            $table->string('mobile_phone', 100)->nullable();
            $table->string('email_address', 255)->nullable();
            $table->string('purchasing_officer', 255)->nullable();
            $table->string('purchase_mobile_phone', 100)->nullable();
            $table->string('purchase_email_address', 255)->nullable();
            $table->string('payment_authority', 255)->nullable();
            $table->string('payment_authority_mobile_phone', 100)->nullable();
            $table->string('payment_authority_email_address', 255)->nullable();
            $table->string('accounting_contact_name', 255)->nullable();
            $table->string('accounting_contact_name', 255)->nullable();
            $table->string('accounting_gsm', 100)->nullable();
            $table->string('accounting_email', 100)->nullable();
            $table->integer('user_id', false, false)->nullable();
            $table->enum('actor_type', ['dealer', 'subdealer'])->nullable();
            $table->string('token', 255)->nullable();
            $table->string('sms_code', 100)->nullable();
            $table->string('sms_explorer_message_id', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('contract_bank_accounts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('contract_id', false, false);
            $table->string('bank_name', 255)->nullable();
            $table->string('branch', 255)->nullable();
            $table->string('account_no', 255)->nullable();
            $table->string('account_holder', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('contract_emails', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('contract_id', false, false);
            $table->string('email', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('contract_gsms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('contract_id', false, false);
            $table->string('gsm', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('contract_ship_locations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('contract_id', false, false);
            $table->string('name', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('district', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('fax', 255)->nullable();
            $table->string('authorized_name', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('contract_signatures', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('user_id', false, false);
            $table->enum('actor_type', ['dealer', 'subdealer'])->nullable();
            $table->bigInteger('template_id', false, false);
            $table->dateTime('signed_at')->nullable();
            $table->string('ip_address', 100);
            $table->string('sms_code', 100);
            $table->string('sms_message_id', 255);
            $table->enum('status', ['pending', 'verified'])->nullable();
            $table->string('token', 255)->nullable();
            $table->string('pdf_path', 100)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_signatures');
        Schema::dropIfExists('contract_ship_locations');
        Schema::dropIfExists('contract_gsms');
        Schema::dropIfExists('contract_emails');
        Schema::dropIfExists('contract_bank_accounts');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_templates');
    }
};
