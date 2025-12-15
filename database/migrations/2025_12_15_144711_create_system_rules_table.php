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
        Schema::create('system_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'unit_occupancy', 'pool_weekly_guests', 'pool_monthly_guests'
            $table->string('name'); // Nombre descriptivo de la regla
            $table->text('description')->nullable();
            $table->json('conditions')->nullable(); // Condiciones específicas (día de semana, cantidad de habitantes, etc.)
            $table->json('limits'); // Límites (max_residents, max_guests, etc.)
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->integer('priority')->default(0); // Para resolver conflictos (mayor = más prioridad)
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index(['valid_from', 'valid_to']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_rules');
    }
};
