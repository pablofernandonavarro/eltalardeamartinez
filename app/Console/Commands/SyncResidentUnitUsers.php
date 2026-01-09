<?php

namespace App\Console\Commands;

use App\Models\Resident;
use App\Models\UnitUser;
use Illuminate\Console\Command;

class SyncResidentUnitUsers extends Command
{
    protected $signature = 'residents:sync-unit-users';

    protected $description = 'Sincronizar unit_users para residentes con auth_user_id que no tienen entrada';

    public function handle(): int
    {
        $this->info('Sincronizando residentes con unit_users...');

        // Encontrar residentes activos con auth_user_id que NO tienen entrada en unit_users
        $residents = Resident::query()
            ->whereNotNull('auth_user_id')
            ->active()
            ->get();

        $synced = 0;
        $skipped = 0;

        foreach ($residents as $resident) {
            // Verificar si ya existe en unit_users
            $exists = UnitUser::where('unit_id', $resident->unit_id)
                ->where('user_id', $resident->auth_user_id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Crear entrada en unit_users
            UnitUser::create([
                'unit_id' => $resident->unit_id,
                'user_id' => $resident->auth_user_id,
                'is_responsible' => false,
                'started_at' => $resident->started_at ?? now(),
            ]);

            $this->line("✓ {$resident->name} agregado a unit_users (Unidad {$resident->unit->full_identifier})");
            $synced++;
        }

        $this->info("\n✅ Sincronización completada:");
        $this->info("   - Agregados: {$synced}");
        $this->info("   - Ya existían: {$skipped}");

        return Command::SUCCESS;
    }
}
