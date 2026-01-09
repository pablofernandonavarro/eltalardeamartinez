<?php

namespace App\Console\Commands;

use App\Models\Resident;
use App\Models\User;
use App\Models\UnitUser;
use Illuminate\Console\Command;

class FixPabloUser extends Command
{
    protected $signature = 'fix:pablo-user';

    protected $description = 'Fix Pablo Fernando Navarro user and unit association';

    public function handle(): int
    {
        $this->info('Diagnosticando Pablo Fernando Navarro...');
        
        // Buscar residente Pablo
        $resident = Resident::where('name', 'like', '%Pablo%')
            ->where('name', 'like', '%Navarro%')
            ->first();
        
        if (!$resident) {
            $this->error('No se encontró el residente Pablo Fernando Navarro');
            return Command::FAILURE;
        }
        
        $this->line("Residente encontrado:");
        $this->line("  ID: {$resident->id}");
        $this->line("  Nombre: {$resident->name}");
        $this->line("  Unidad: {$resident->unit->full_identifier}");
        $this->line("  Unit ID: {$resident->unit_id}");
        $this->line("  Auth User ID: " . ($resident->auth_user_id ?? 'NULL'));
        $this->line("  Email: " . ($resident->email ?? 'NULL'));
        
        // Buscar usuario con email pablofernandonavarro@gmail.com
        $user = User::where('email', 'pablofernandonavarro@gmail.com')->first();
        
        if (!$user) {
            $this->error('No se encontró usuario con email pablofernandonavarro@gmail.com');
            return Command::FAILURE;
        }
        
        $this->line("\nUsuario encontrado:");
        $this->line("  ID: {$user->id}");
        $this->line("  Email: {$user->email}");
        $this->line("  Unidades: " . $user->currentUnitUsers()->count());
        
        // Fix: Vincular residente con usuario
        if ($resident->auth_user_id != $user->id) {
            $this->line("\n🔧 Vinculando residente con usuario...");
            $resident->auth_user_id = $user->id;
            $resident->save();
            $this->info("✓ Residente vinculado con usuario");
        }
        
        // Fix: Agregar usuario a unit_users
        $unitUser = UnitUser::where('unit_id', $resident->unit_id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$unitUser) {
            $this->line("\n🔧 Agregando usuario a unit_users...");
            UnitUser::create([
                'unit_id' => $resident->unit_id,
                'user_id' => $user->id,
                'is_responsible' => false,
                'started_at' => $resident->started_at ?? now(),
            ]);
            $this->info("✓ Usuario agregado a unit_users");
        } else {
            $this->info("✓ Usuario ya está en unit_users");
        }
        
        // Generar QR si no lo tiene
        if (!$resident->qr_token) {
            $this->line("\n🔧 Generando QR...");
            $resident->generateQrToken();
            $this->info("✓ QR generado");
        }
        
        $this->info("\n✅ Fix completado exitosamente");
        return Command::SUCCESS;
    }
}
