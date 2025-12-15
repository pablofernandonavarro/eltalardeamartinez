<?php

namespace Database\Seeders;

use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user (admins are always approved, without 2FA for easier access)
        User::factory()->withoutTwoFactor()->create([
            'name' => 'Administrador',
            'email' => 'admin@eltalardemartinez.com',
            'role' => Role::Admin,
            'approved_at' => now(),
        ]);

        // Create example propietario (approved)
        User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'role' => Role::Propietario,
            'approved_at' => now(),
        ]);

        // Create example inquilino (approved)
        User::factory()->create([
            'name' => 'María González',
            'email' => 'maria.gonzalez@example.com',
            'role' => Role::Inquilino,
            'approved_at' => now(),
        ]);

        // Seed complexes, buildings, and units
        $this->call([
            ComplexSeeder::class,
            ConceptSeeder::class,
            PoolSeeder::class,
        ]);
    }
}
