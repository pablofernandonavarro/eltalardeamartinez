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
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la amenidad
            $table->string('slug')->unique(); // Para identificar en codigo
            $table->text('description')->nullable(); // Descripción
            $table->string('icon_color')->default('blue'); // Color del icono (blue, orange, green, etc.)
            $table->string('schedule_type')->nullable(); // Tipo: weekdays, weekends, all_days, by_reservation
            $table->string('weekday_schedule')->nullable(); // Ej: "9:00-13:00,15:00-22:00"
            $table->string('weekend_schedule')->nullable(); // Ej: "10:00-20:00"
            $table->string('availability')->nullable(); // Ej: "Temporada de Verano", "Todo el Año"
            $table->text('additional_info')->nullable(); // Info adicional/importante
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0); // Orden de visualización
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
