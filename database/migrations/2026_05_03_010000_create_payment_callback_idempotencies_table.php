<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_callback_idempotencies', function (Blueprint $table) {
            $table->id();
            $table->string('flow_type', 32);
            $table->string('model_type', 32);
            $table->unsignedBigInteger('model_id');
            $table->unsignedInteger('bank_integration_id')->nullable();
            $table->string('provider_reference', 255);
            $table->string('status', 20)->default('processing');
            $table->boolean('result_success')->nullable();
            $table->text('result_message')->nullable();
            $table->json('resolved_payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['flow_type', 'model_type', 'model_id', 'provider_reference'],
                'payment_callback_idemp_unique'
            );
            $table->index('provider_reference', 'payment_callback_idemp_provider_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_callback_idempotencies');
    }
};
