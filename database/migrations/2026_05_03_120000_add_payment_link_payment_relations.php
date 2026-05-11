<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->unsignedInteger('current_payment_id')->nullable()->after('payment_date');
            $table->unsignedInteger('paid_payment_id')->nullable()->after('current_payment_id');
            $table->index('current_payment_id', 'payment_links_current_payment_id_idx');
            $table->index('paid_payment_id', 'payment_links_paid_payment_id_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('payment_link_id')->nullable()->after('sub_dealer_id');
            $table->index('payment_link_id', 'payments_payment_link_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_payment_link_id_idx');
            $table->dropColumn('payment_link_id');
        });

        Schema::table('payment_links', function (Blueprint $table) {
            $table->dropIndex('payment_links_current_payment_id_idx');
            $table->dropIndex('payment_links_paid_payment_id_idx');
            $table->dropColumn(['current_payment_id', 'paid_payment_id']);
        });
    }
};
