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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->string('floor')->nullable();
            $table->decimal('coefficient', 8, 4)->default(1.0000);
            $table->integer('rooms')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('owner')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
