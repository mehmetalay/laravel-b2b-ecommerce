<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_dealers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('username', 255)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 255)->nullable();
            $table->bigInteger('dealer_id', false, false);
            $table->tinyInteger('can_place_order', false, false)->nullable()->default(1);
            $table->tinyInteger('can_approve_order', false, false)->nullable()->default(0);
            $table->tinyInteger('can_record_payment', false, false)->nullable()->default(0);
            $table->tinyInteger('can_view_prices', false, false)->nullable()->default(1);
            $table->tinyInteger('password_must_change', false, false)->nullable()->default(0);
            $table->string('password_reset_code', 255)->nullable();
            $table->dateTime('password_reset_expires_at')->nullable();
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->dateTime('last_login_date')->nullable();
            $table->string('last_login_ip', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('dealer_id', false, false)->nullable();
            $table->bigInteger('sub_dealer_id', false, false)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('tax_office', 100)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('phone', 100)->nullable();
            $table->integer('city_id', false, false);
            $table->integer('district_id', false, false);
            $table->integer('neighborhood_id', false, false);
            $table->text('address')->nullable();
            $table->tinyInteger('is_default', false, false)->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('dealer_applications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('company_name', 255)->nullable();
            $table->string('tax_office', 255)->nullable();
            $table->string('tax_number', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('district', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('authorized_name_surname', 255)->nullable();
            $table->string('identity_number', 100)->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->string('mobile_phone_number', 100)->nullable();
            $table->string('fax_number', 100)->nullable();
            $table->string('email_address', 255)->nullable();
            $table->string('web_address', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->tinyInteger('email_sent', false, false)->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('dealer_application_documents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->integer('dealer_application_id', false, false)->nullable();
            $table->string('path', 255)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_application_documents');
        Schema::dropIfExists('dealer_applications');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('sub_dealers');
    }
};
