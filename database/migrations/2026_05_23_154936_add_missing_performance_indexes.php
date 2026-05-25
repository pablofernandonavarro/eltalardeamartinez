<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // pool_entries: reporting por pool+fecha y lookups por residente
        Schema::table('pool_entries', function (Blueprint $table) {
            $table->index(['pool_id', 'entered_at', 'exited_at'], 'pool_entries_pool_date_range_idx');
            $table->index('resident_id', 'pool_entries_resident_idx');
        });

        // sum_payments: lookups por reserva y stats por fecha+estado
        Schema::table('sum_payments', function (Blueprint $table) {
            $table->index('reservation_id', 'sum_payments_reservation_idx');
            $table->index(['created_at', 'status'], 'sum_payments_date_status_idx');
        });

        // payments: conteo de pendientes en el dashboard
        Schema::table('payments', function (Blueprint $table) {
            $table->index('status', 'payments_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pool_entries', function (Blueprint $table) {
            $table->dropIndex('pool_entries_pool_date_range_idx');
            $table->dropIndex('pool_entries_resident_idx');
        });

        Schema::table('sum_payments', function (Blueprint $table) {
            $table->dropIndex('sum_payments_reservation_idx');
            $table->dropIndex('sum_payments_date_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_idx');
        });
    }
};
