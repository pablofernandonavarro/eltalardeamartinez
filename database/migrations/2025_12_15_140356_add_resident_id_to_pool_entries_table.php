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
        Schema::table('pool_entries', function (Blueprint $table) {
            // Hacer user_id nullable porque puede ser un residente
            $table->foreignId('user_id')->nullable()->change();
            
            // Agregar resident_id
            $table->foreignId('resident_id')->nullable()->after('user_id')
                ->constrained()->onDelete('cascade');
            
            // Asegurar que al menos uno de user_id o resident_id esté presente
            // Esto se validará a nivel de aplicación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pool_entries', function (Blueprint $table) {
            $table->dropForeign(['resident_id']);
            $table->dropColumn('resident_id');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
