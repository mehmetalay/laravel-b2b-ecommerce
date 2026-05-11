<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('use_dates', false, false)->nullable()->default(1);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->tinyInteger('is_active', false, false)->nullable()->default(1);
            $table->integer('created_by', false, false)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigInteger('id', true, false);
            $table->bigInteger('survey_id', false, false);
            $table->text('question')->nullable();
            $table->enum('type', ['single', 'multiple', 'text'])->nullable();
            $table->integer('sort_order', false, false)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('survey_question_id', false, false);
            $table->string('option_text', 255)->nullable();
            $table->integer('sort_order', false, false)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id', true, false);
            $table->bigInteger('survey_id', false, false);
            $table->bigInteger('survey_question_id', false, false);
            $table->bigInteger('survey_option_id', false, false)->nullable();
            $table->bigInteger('dealer_id', false, false);
            $table->longText('answer_text')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
