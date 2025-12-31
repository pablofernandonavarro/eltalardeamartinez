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
        Schema::table('residents', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('invitation_token')->unique()->nullable()->after('qr_token');
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['email', 'invitation_token', 'invitation_sent_at']);
        });
    }
};
