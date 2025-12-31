<?php

namespace App\Console\Commands;

use App\Models\Unit;
use App\Models\UnitUser;
use Illuminate\Console\Command;

class FixDuplicatePaymentResponsibles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:duplicate-payment-responsibles {--dry-run : Mostrar qué se haría sin aplicar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige unidades con más de un responsable de pago, dejando solo al propietario como responsable';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Modo DRY RUN - No se aplicarán cambios');
        }

        $this->info('Buscando unidades con múltiples responsables de pago...');

        // Obtener todas las unidades
        $units = Unit::all();
        $fixed = 0;

        foreach ($units as $unit) {
            // Buscar asignaciones activas con responsable de pago
            $responsibles = UnitUser::where('unit_id', $unit->id)
                ->where('is_responsible', true)
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->with('user')
                ->get();

            if ($responsibles->count() > 1) {
                $this->warn("\nUnidad {$unit->full_identifier} tiene {$responsibles->count()} responsables:");

                foreach ($responsibles as $resp) {
                    $role = $resp->user->role?->label() ?? 'Sin rol';
                    $this->line("  - {$resp->user->name} ({$role}) - Propietario: ".($resp->is_owner ? 'Sí' : 'No'));
                }

                // Prioridad: mantener al propietario como responsable
                $owner = $responsibles->where('is_owner', true)->first();

                if ($owner) {
                    $this->info("  → Manteniendo al PROPIETARIO como responsable: {$owner->user->name}");

                    // Quitar responsabilidad a los demás
                    foreach ($responsibles as $resp) {
                        if ($resp->id !== $owner->id) {
                            $this->line("  → Removiendo responsabilidad de: {$resp->user->name}");
                            if (! $dryRun) {
                                $resp->update(['is_responsible' => false]);
                            }
                        }
                    }
                } else {
                    // Si no hay propietario, mantener al primero
                    $first = $responsibles->first();
                    $this->info("  → No hay propietario, manteniendo al primero: {$first->user->name}");

                    foreach ($responsibles as $resp) {
                        if ($resp->id !== $first->id) {
                            $this->line("  → Removiendo responsabilidad de: {$resp->user->name}");
                            if (! $dryRun) {
                                $resp->update(['is_responsible' => false]);
                            }
                        }
                    }
                }

                $fixed++;
            }
        }

        if ($fixed === 0) {
            $this->info('\n✓ No se encontraron unidades con múltiples responsables.');
        } else {
            if ($dryRun) {
                $this->warn("\n⚠ Se encontraron {$fixed} unidades con problemas. Ejecuta sin --dry-run para corregir.");
            } else {
                $this->info("\n✓ Se corrigieron {$fixed} unidades.");
            }
        }

        return 0;
    }
}
