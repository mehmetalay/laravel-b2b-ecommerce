<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->enum('type', ['slider', 'payment_slider', 'category_slider', 'campaign_slider'])->nullable();
            $table->string('image_desktop_tr', 255)->nullable();
            $table->string('image_desktop_en', 255)->nullable();
            $table->string('image_tablet_tr', 255)->nullable();
            $table->string('image_tablet_en', 255)->nullable();
            $table->string('image_mobile_tr', 255)->nullable();
            $table->string('image_mobile_en', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->tinyInteger('target_blank', false, false)->nullable()->default(0);
            $table->integer('sort_order', false, false)->nullable();
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('homepage_blocks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->string('title_tr', 255)->nullable();
            $table->mediumText('subtitle_tr')->nullable();
            $table->string('title_en', 255)->nullable();
            $table->mediumText('subtitle_en')->nullable();
            $table->tinyInteger('is_active', false, false)->nullable()->default(1);
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->string('slug', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('homepage_block_products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('homepage_block_id', false, false);
            $table->bigInteger('product_id', false, false);
            $table->integer('sort_order', false, false)->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_block_products');
        Schema::dropIfExists('homepage_blocks');
        Schema::dropIfExists('sliders');
    }
};
