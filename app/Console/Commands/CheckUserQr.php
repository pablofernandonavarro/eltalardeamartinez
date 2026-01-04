<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUserQr extends Command
{
    protected $signature = 'user:check-qr {email}';
    protected $description = 'Verificar y generar QR para un usuario';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuario no encontrado: {$email}");
            return 1;
        }
        
        $this->info("Usuario encontrado:");
        $this->info("ID: {$user->id}");
        $this->info("Nombre: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Aprobado: " . ($user->approved_at ? 'Sí' : 'No'));
        $this->info("QR Token actual: " . ($user->qr_token ?? 'NULL'));
        
        if (!$user->qr_token) {
            $this->warn("El usuario NO tiene QR token. Generando...");
            $user->qr_token = (string) \Illuminate\Support\Str::uuid();
            $user->save();
            $this->info("✅ QR generado: {$user->qr_token}");
        } else {
            $this->info("✅ El usuario ya tiene QR token");
        }
        
        // Verificar unidades
        $unitUsers = $user->currentUnitUsers()->with('unit')->get();
        if ($unitUsers->count() > 0) {
            $this->info("\nUnidades activas:");
            foreach ($unitUsers as $uu) {
                $this->info("  - {$uu->unit->full_identifier}");
            }
        } else {
            $this->warn("El usuario no tiene unidades activas");
        }
        
        return 0;
    }
}
