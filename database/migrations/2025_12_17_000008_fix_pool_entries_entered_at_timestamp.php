<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En MySQL/MariaDB, los TIMESTAMP pueden tener comportamiento implícito ON UPDATE.
        // Pasamos a DATETIME para que entered_at nunca sea modificado al registrar exited_at.
        //
        // Importante: en tests se usa SQLite, donde "MODIFY" no existe, así que ahí no hacemos nada.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE pool_entries MODIFY entered_at DATETIME NOT NULL');
        DB::statement('ALTER TABLE pool_entries MODIFY exited_at DATETIME NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Volver a TIMESTAMP (no recomendado, pero para rollback)
        DB::statement('ALTER TABLE pool_entries MODIFY entered_at TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE pool_entries MODIFY exited_at TIMESTAMP NULL');
    }
};
