<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pool_entry_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_entry_id')->constrained('pool_entries')->cascadeOnDelete();
            $table->foreignId('pool_guest_id')->constrained('pool_guests')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pool_entry_id', 'pool_guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_entry_guests');
    }
};
