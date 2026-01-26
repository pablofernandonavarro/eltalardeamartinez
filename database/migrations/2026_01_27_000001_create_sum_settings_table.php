<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sum_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('sum_settings')->insert([
            [
                'key' => 'price_per_hour',
                'value' => '500',
                'description' => 'Precio por hora de alquiler del SUM',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'open_time',
                'value' => '09:00',
                'description' => 'Hora de apertura del SUM',
                'type' => 'time',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'close_time',
                'value' => '23:00',
                'description' => 'Hora de cierre del SUM',
                'type' => 'time',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_days_advance',
                'value' => '30',
                'description' => 'Dias maximos de anticipacion para reservar',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_hours_notice',
                'value' => '24',
                'description' => 'Horas minimas de aviso previo',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'requires_approval',
                'value' => 'false',
                'description' => 'Si las reservas requieren aprobacion del administrador',
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
        Schema::dropIfExists('sum_settings');
    }
};
