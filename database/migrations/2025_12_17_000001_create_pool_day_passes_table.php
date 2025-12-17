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
        Schema::create('pool_day_passes', function (Blueprint $table) {
            $table->id();

            $table->uuid('token')->unique();
            $table->date('date');

            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            // Titular del pase (usuario o residente)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();

            // Invitados precargados por el usuario para ese día
            $table->unsignedInteger('guests_allowed')->default(0);

            // Uso (1 vez por día)
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('used_pool_id')->nullable()->constrained('pools')->nullOnDelete();
            $table->unsignedInteger('used_guests_count')->nullable();
            $table->foreignId('pool_entry_id')->nullable()->constrained('pool_entries')->nullOnDelete();

            $table->timestamps();

            $table->index(['date', 'unit_id']);
            $table->index(['date', 'user_id']);
            $table->index(['date', 'resident_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pool_day_passes');
    }
};
