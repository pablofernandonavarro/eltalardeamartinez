<?php

namespace App\Console\Commands;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Console\Command;

class LinkUsersToResidents extends Command
{
    protected $signature = 'residents:link-users';

    protected $description = 'Vincular usuarios con residentes por email';

    public function handle(): int
    {
        $this->info('Buscando residentes sin auth_user_id...');
        
        // Residentes activos sin auth_user_id pero con email
        $residents = Resident::query()
            ->whereNull('auth_user_id')
            ->whereNotNull('email')
            ->active()
            ->get();
        
        $linked = 0;
        $notFound = 0;
        
        foreach ($residents as $resident) {
            // Buscar usuario con el mismo email
            $user = User::where('email', $resident->email)->first();
            
            if ($user) {
                $resident->auth_user_id = $user->id;
                $resident->save();
                
                // Generar QR si no lo tiene
                if (!$resident->qr_token && $resident->canHavePersonalQr()) {
                    $resident->generateQrToken();
                }
                
                $this->line("✓ {$resident->name} vinculado con {$user->email}");
                $linked++;
            } else {
                $this->line("✗ {$resident->name} ({$resident->email}) - usuario no encontrado");
                $notFound++;
            }
        }
        
        $this->info("\n✅ Proceso completado:");
        $this->info("   - Vinculados: {$linked}");
        $this->info("   - No encontrados: {$notFound}");
        
        return Command::SUCCESS;
    }
}
