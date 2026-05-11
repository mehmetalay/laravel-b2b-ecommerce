<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additional_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->integer('decimal', false, false)->nullable()->default(2);
            $table->string('admin_password', 255)->nullable();
            $table->integer('purchase_limit_minimum', false, false)->nullable()->default(1);
            $table->integer('purchase_limit_maximum', false, false)->nullable()->default(1);
            $table->tinyInteger('site_status', false, false)->nullable()->default(1);
            $table->string('coming_soon_title', 255)->nullable();
            $table->text('coming_soon_text')->nullable();
            $table->integer('min_stock_quantity', false, false)->nullable()->default(1);
            $table->integer('max_stock_quantity', false, false)->nullable();
            $table->tinyInteger('display_of_out_of_stock_products', false, false)->nullable()->default(0);
            $table->tinyInteger('show_stock', false, false)->nullable()->default(1);
            $table->integer('product_record_per_page', false, false)->nullable()->default(20);
            $table->integer('maximum_stock_number_display_user', false, false)->nullable();
            $table->integer('maximum_stock_number_display_plasiyer', false, false)->nullable();
            $table->text('order_emails')->nullable();
            $table->text('payment_emails')->nullable();
            $table->text('dealer_application_mails')->nullable();
            $table->tinyInteger('use_contract_approval', false, false)->nullable()->default(0);
            $table->integer('default_company_id', false, false)->nullable()->default(1);
            $table->tinyInteger('cart_item_note_visibility', false, false)->nullable()->default(1);
            $table->tinyInteger('payment_plan_selection', false, false)->nullable()->default(1);
            $table->tinyInteger('payment_plan_required', false, false)->nullable()->default(1);
            $table->tinyInteger('payment_type_selection', false, false)->nullable()->default(1);
            $table->tinyInteger('payment_type_required', false, false)->nullable()->default(1);
            $table->tinyInteger('delivery_type_selection', false, false)->nullable()->default(1);
            $table->tinyInteger('delivery_type_required', false, false)->nullable()->default(1);
            $table->bigInteger('allow_over_order', false, false)->nullable()->default(0);
            $table->tinyInteger('is_critical_stock_enabled', false, false)->nullable()->default(1);
            $table->integer('critical_stock_threshold', false, false)->nullable()->default(5);
            $table->tinyInteger('is_order_confirmation', false, false)->nullable()->default(1);
            $table->enum('default_product_view_type', ['grid', 'list'])->nullable()->default('grid');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('general_infos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('company_name', 255)->nullable();
            $table->string('company_official_name', 255)->nullable();
            $table->string('company_website', 255)->nullable();
            $table->string('authorized_person', 255)->nullable();
            $table->string('company_phone_number', 100)->nullable();
            $table->string('company_phone_number_2', 100)->nullable();
            $table->string('company_mobile_number', 100)->nullable();
            $table->string('fax_number', 100)->nullable();
            $table->string('email_address', 100)->nullable();
            $table->string('email_address_2', 100)->nullable();
            $table->string('company_full_address', 255)->nullable();
            $table->string('seo_meta_title', 255)->nullable();
            $table->text('seo_meta_description')->nullable();
            $table->text('seo_meta_keywords')->nullable();
            $table->string('google_maps_link', 255)->nullable();
            $table->text('google_maps_embed')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('theme_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->text('copyright')->nullable();
            $table->text('copyright_en')->nullable();
            $table->text('footer_about_us_text')->nullable();
            $table->text('footer_about_us_text_en')->nullable();
            $table->string('footer_social_title', 100)->nullable();
            $table->text('facebook')->nullable();
            $table->text('instagram')->nullable();
            $table->text('twitter')->nullable();
            $table->text('pinterest')->nullable();
            $table->text('youtube')->nullable();
            $table->text('linkedin')->nullable();
            $table->text('whatsapp')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('batch_locks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('job_name', 255)->nullable();
            $table->tinyInteger('is_running', false, false)->nullable()->default(0);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('entity_last_updates', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->string('entity_type', 50);
            $table->timestamp('last_update_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique('entity_type', 'entity_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_last_updates');
        Schema::dropIfExists('batch_locks');
        Schema::dropIfExists('theme_settings');
        Schema::dropIfExists('general_infos');
        Schema::dropIfExists('additional_settings');
    }
};
