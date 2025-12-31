<?php

namespace App\Console\Commands;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateResidentUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resident:create-user {resident_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user account for an existing resident';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $residentId = $this->argument('resident_id');

        $resident = Resident::with(['unit', 'user'])->find($residentId);

        if (! $resident) {
            $this->error('Residente no encontrado.');

            return 1;
        }

        if ($resident->isMinor()) {
            $this->error('El residente es menor de 18 años y no puede tener una cuenta.');

            return 1;
        }

        if ($resident->auth_user_id) {
            $this->error('Este residente ya tiene una cuenta de usuario.');

            return 1;
        }

        $this->info("Residente: {$resident->name}");
        $this->info("Unidad: {$resident->unit->full_identifier}");
        $this->info("Responsable: {$resident->user->name}");
        $this->newLine();

        $email = $this->ask('Email para la cuenta');
        $password = $this->secret('Contraseña (mínimo 8 caracteres)');

        if (strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');

            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe un usuario con ese email.');

            return 1;
        }

        // Crear usuario (sin rol, el rol se determina por unit_users)
        $user = User::create([
            'name' => $resident->name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => null, // Los residentes no tienen rol directo
            'approved_at' => now(),
        ]);

        // Vincular residente con usuario
        $resident->auth_user_id = $user->id;
        $resident->save();

        // Generar QR automáticamente
        $resident->generateQrToken();

        $this->newLine();
        $this->info('✓ Usuario creado exitosamente');
        $this->info("✓ Email: {$email}");
        $this->info('✓ QR personal generado');

        return 0;
    }
}
