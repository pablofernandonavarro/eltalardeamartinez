<?php

namespace App\Console\Commands;

use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Console\Command;

class CreateResidentFromUser extends Command
{
    protected $signature = 'resident:create-from-user {email}';
    protected $description = 'Crear registro de residente desde un usuario existente';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuario no encontrado: {$email}");
            return 1;
        }
        
        // Verificar si ya existe como residente
        $existingResident = Resident::where('auth_user_id', $user->id)->first();
        if ($existingResident) {
            $this->info("El usuario ya tiene un registro de residente (ID: {$existingResident->id})");
            return 0;
        }
        
        // Buscar la unidad del usuario
        $unitUser = \DB::table('unit_users')
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();
            
        if (!$unitUser) {
            $this->error("El usuario no está asociado a ninguna unidad activa");
            return 1;
        }
        
        $unit = Unit::find($unitUser->unit_id);
        if (!$unit) {
            $this->error("Unidad no encontrada");
            return 1;
        }
        
        // Solicitar fecha de nacimiento
        $birthDate = $this->ask('Fecha de nacimiento (YYYY-MM-DD)');
        
        // Crear residente
        $resident = Resident::create([
            'unit_id' => $unit->id,
            'user_id' => $user->id, // Usuario responsable (él mismo)
            'auth_user_id' => $user->id, // Vinculado a su propia cuenta
            'name' => $user->name,
            'email' => $user->email,
            'birth_date' => $birthDate,
            'relationship' => 'Titular',
            'started_at' => now(),
        ]);
        
        // Generar QR si es mayor de 15 años
        if ($resident->canHavePersonalQr()) {
            $resident->generateQrToken();
            $this->info("✅ Residente creado con QR personal (ID: {$resident->id})");
            $this->info("QR Token: {$resident->qr_token}");
        } else {
            $this->info("✅ Residente creado (ID: {$resident->id}) - Sin QR (menor de 15 años)");
        }
        
        return 0;
    }
}
