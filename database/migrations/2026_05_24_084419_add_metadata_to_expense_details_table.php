<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_details', function (Blueprint $table) {
            // metadata: almacena datos adicionales del PDF de liquidación por unidad.
            // Claves esperadas: previous_balance, payments_period, bonification,
            //                   accumulated_debt, interests, total (TOTAL del período).
            $table->json('metadata')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_details', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
