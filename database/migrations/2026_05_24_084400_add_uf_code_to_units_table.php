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
        Schema::table('units', function (Blueprint $table) {
            // uf_code: código único de la unidad funcional del prorrateo (ej: "0001")
            // Se agrega después de building_id; nullable para no romper registros existentes.
            $table->string('uf_code', 10)->nullable()->unique()->after('building_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique(['uf_code']);
            $table->dropColumn('uf_code');
        });
    }
};
