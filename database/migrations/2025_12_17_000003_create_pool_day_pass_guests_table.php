<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pool_day_pass_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_day_pass_id')->constrained('pool_day_passes')->cascadeOnDelete();
            $table->foreignId('pool_guest_id')->constrained('pool_guests')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pool_day_pass_id', 'pool_guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_day_pass_guests');
    }
};
