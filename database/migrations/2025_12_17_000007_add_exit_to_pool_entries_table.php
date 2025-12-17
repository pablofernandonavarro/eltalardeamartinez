<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pool_entries', function (Blueprint $table) {
            $table->timestamp('exited_at')->nullable()->after('entered_at');
            $table->foreignId('exited_by_user_id')->nullable()->after('exited_at')
                ->constrained('users')->nullOnDelete();
            $table->text('exit_notes')->nullable()->after('notes');

            $table->index(['pool_id', 'exited_at']);
            $table->index(['unit_id', 'exited_at']);
        });
    }

    public function down(): void
    {
        Schema::table('pool_entries', function (Blueprint $table) {
            $table->dropIndex(['pool_id', 'exited_at']);
            $table->dropIndex(['unit_id', 'exited_at']);

            $table->dropForeign(['exited_by_user_id']);
            $table->dropColumn(['exited_at', 'exited_by_user_id', 'exit_notes']);
        });
    }
};
