<?php

namespace App\Console\Commands;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Console\Command;

class FixResidentRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resident:fix-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix roles for resident users (set to residente)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando usuarios residentes con roles incorrectos...');

        // Obtener todos los IDs de usuarios que son residentes
        $residentUserIds = Resident::query()
            ->whereNotNull('auth_user_id')
            ->pluck('auth_user_id')
            ->unique();

        if ($residentUserIds->isEmpty()) {
            $this->info('No hay usuarios residentes registrados.');

            return 0;
        }

        // Buscar usuarios residentes que no tengan el rol 'residente'
        $usersToUpdate = User::query()
            ->whereIn('id', $residentUserIds)
            ->where(function ($query) {
                $query->whereNull('role')
                      ->orWhere('role', '!=', 'residente');
            })
            ->get();

        if ($usersToUpdate->isEmpty()) {
            $this->info('✓ Todos los usuarios residentes ya tienen el rol correcto (residente).');

            return 0;
        }

        $this->info("Encontrados {$usersToUpdate->count()} usuarios residentes con rol incorrecto.");
        $this->newLine();

        $bar = $this->output->createProgressBar($usersToUpdate->count());
        $bar->start();

        $updated = 0;
        foreach ($usersToUpdate as $user) {
            $user->update(['role' => 'residente']);
            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        $this->info("✓ Se actualizaron {$updated} usuarios residentes.");
        $this->info('✓ Ahora todos los residentes tienen role = residente.');

        return 0;
    }
}
