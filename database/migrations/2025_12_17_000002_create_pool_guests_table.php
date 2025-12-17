<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pool_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'created_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_guests');
    }
};
