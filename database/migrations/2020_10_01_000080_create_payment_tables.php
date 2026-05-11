<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_integrations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('name', 100)->nullable();
            $table->string('bank_code', 100)->nullable();
            $table->text('json')->nullable();
            $table->tinyInteger('automatic_single_payment', false, false)->nullable()->default(0);
            $table->text('message')->nullable();
            $table->string('erp_bank_code', 100)->nullable();
            $table->integer('company_id', false, false)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('logo_path', 100)->nullable();
            $table->tinyInteger('is_active', false, false)->nullable()->default(1);
            $table->tinyInteger('status', false, false)->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->integer('bank_integration_id', false, false);
            $table->integer('installment', false, false)->default(0);
            $table->integer('plus_installment', false, false)->nullable()->default(0);
            $table->integer('commission_rate', false, false)->nullable()->default(0);
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique(['bank_integration_id', 'installment'], 'bank_integration_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('user_id', false, false)->nullable();
            $table->bigInteger('plasiyer_id', false, false)->nullable();
            $table->bigInteger('sub_dealer_id', false, false)->nullable();
            $table->enum('creator_type', ['salesman', 'dealer', 'subdealer'])->nullable();
            $table->string('oid', 255)->nullable();
            $table->integer('bank_integration_id', false, false)->nullable();
            $table->decimal('entered_amount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('amount_paid', 20, 10, false)->nullable()->default('0.0000000000');
            $table->integer('installment', false, false)->nullable()->default(0);
            $table->integer('plus_installment', false, false)->nullable()->default(0);
            $table->integer('commission_rate', false, false)->default(0);
            $table->decimal('commission_amount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->string('card_name', 255)->nullable();
            $table->string('card_number', 255)->nullable();
            $table->text('explanation')->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->tinyInteger('option_3d_payment', false, false)->nullable()->default(1);
            $table->enum('status', ['SUCCESS', 'FAILED', 'PENDING'])->nullable()->default('PENDING');
            $table->dateTime('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('transaction_ref', 255)->nullable();
            $table->tinyInteger('email_sent', false, false)->nullable()->default(0);
            $table->dateTime('email_sent_at')->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->tinyInteger('receipt_issued', false, false)->nullable()->default(0);
            $table->decimal('usd_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('eur_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('gbp_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->enum('refund_status', ['cancelled', 'refunded'])->nullable();
            $table->dateTime('refund_date')->nullable();
            $table->string('erp_document_no', 255)->nullable();
            $table->enum('erp_status', ['pending', 'processing', 'sent', 'failed'])->nullable()->default('sent');
            $table->dateTime('erp_processing_at')->nullable();
            $table->dateTime('erp_synced_at')->nullable();
            $table->integer('erp_attempts', false, false)->nullable()->default(0);
            $table->longText('erp_last_error')->nullable();
            $table->dateTime('erp_last_failed_at')->nullable();
            $table->string('provider_reference', 255)->nullable();
            $table->string('provider_auth_code', 255)->nullable();
            $table->string('provider_rrn', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('payment_links', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('user_id', false, false)->nullable();
            $table->string('token', 255)->nullable();
            $table->decimal('amount', 15, 2, false)->nullable()->default('0.00');
            $table->string('email', 255)->nullable();
            $table->string('phone', 100)->nullable();
            $table->tinyInteger('amount_locked', false, false)->nullable()->default(0);
            $table->tinyInteger('transaction_type', false, false)->nullable()->default(1);
            $table->integer('manual_bank_integration_id', false, false)->nullable();
            $table->integer('manual_installment', false, false)->nullable();
            $table->tinyInteger('manual_lock_bank_installment', false, false)->nullable();
            $table->tinyInteger('is_paid', false, false)->nullable()->default(0);
            $table->dateTime('payment_date')->nullable();
            $table->string('oid', 255)->nullable();
            $table->integer('bank_integration_id', false, false)->nullable();
            $table->decimal('entered_amount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('amount_paid', 20, 10, false)->nullable()->default('0.0000000000');
            $table->integer('installment', false, false)->nullable()->default(0);
            $table->integer('plus_installment', false, false)->nullable()->default(0);
            $table->integer('commission_rate', false, false)->nullable()->default(0);
            $table->decimal('commission_amount', 20, 10, false)->nullable()->default('0.0000000000');
            $table->string('card_name', 255)->nullable();
            $table->string('card_number', 255)->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->text('explanation')->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->tinyInteger('email_sent', false, false)->nullable()->default(0);
            $table->tinyInteger('sms_sent', false, false)->nullable()->default(0);
            $table->integer('sms_explorer_message_id', false, false)->nullable();
            $table->tinyInteger('paid_email_sent', false, false)->nullable()->default(0);
            $table->tinyInteger('paid_sms_sent', false, false)->nullable()->default(0);
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->bigInteger('admin_id', false, false)->nullable();
            $table->bigInteger('plasiyer_id', false, false)->nullable();
            $table->tinyInteger('receipt_issued', false, false)->nullable()->default(0);
            $table->decimal('usd_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('eur_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->decimal('gbp_exchange_rate', 15, 10, false)->nullable()->default('0.0000000000');
            $table->enum('refund_status', ['cancelled', 'refunded'])->nullable();
            $table->dateTime('refund_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_links');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('installments');
        Schema::dropIfExists('bank_integrations');
    }
};
