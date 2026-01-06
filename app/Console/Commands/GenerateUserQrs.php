<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUserQrs extends Command
{
    protected $signature = 'users:generate-qrs';

    protected $description = 'Generar QR tokens para usuarios que tienen unidades pero no tienen QR';

    public function handle(): int
    {
        $this->info('Generando QR tokens para usuarios...');
        
        // Usuarios aprobados con unidades activas pero sin qr_token
        $users = User::query()
            ->whereNotNull('approved_at')
            ->whereNull('qr_token')
            ->whereHas('currentUnitUsers')
            ->get();
        
        $generated = 0;
        
        foreach ($users as $user) {
            $user->qr_token = (string) Str::uuid();
            $user->save();
            
            $this->line("✓ QR generado para {$user->name} ({$user->email})");
            $generated++;
        }
        
        $this->info("\n✅ Proceso completado:");
        $this->info("   - QRs generados: {$generated}");
        
        return Command::SUCCESS;
    }
}
