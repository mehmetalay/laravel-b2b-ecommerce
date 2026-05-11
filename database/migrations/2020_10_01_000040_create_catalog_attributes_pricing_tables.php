<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->string('name', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('attribute_group_id', false, false);
            $table->string('name', 255)->nullable();
            $table->string('name_en', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->tinyInteger('show_in_filter', false, false)->nullable()->default(1);
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('attribute_id', false, false);
            $table->string('name', 255)->nullable();
            $table->string('name_en', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable()->default(1);
            $table->tinyInteger('show_in_filter', false, false)->nullable()->default(1);
            $table->tinyInteger('status', false, false)->nullable()->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('product_id', false, false);
            $table->bigInteger('attribute_value_id', false, false);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique(['product_id', 'attribute_value_id'], 'product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_groups');
    }
};
