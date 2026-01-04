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
        Schema::create('pool_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Clave de configuración');
            $table->string('value')->comment('Valor de configuración');
            $table->string('description')->nullable()->comment('Descripción legible');
            $table->string('type')->default('integer')->comment('Tipo de dato: integer, boolean, string');
            $table->timestamps();
        });

        // Insertar configuraciones por defecto
        DB::table('pool_settings')->insert([
            [
                'key' => 'max_guests_weekday',
                'value' => '4',
                'description' => 'Máximo de invitados permitidos de Lunes a Viernes',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_guests_weekend',
                'value' => '2',
                'description' => 'Máximo de invitados permitidos Sábados, Domingos y Feriados',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'allow_extra_payment',
                'value' => 'false',
                'description' => 'Permitir pagos por invitados extra (true/false)',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pool_settings');
    }
};
