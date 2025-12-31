<?php

namespace App\Console\Commands;

use App\Models\Resident;
use Illuminate\Console\Command;

class ListResidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resident:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all active residents';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $residents = Resident::with(['unit', 'user', 'authUser'])
            ->active()
            ->orderBy('name')
            ->get();

        if ($residents->isEmpty()) {
            $this->info('No hay residentes activos.');

            return 0;
        }

        $data = $residents->map(function ($resident) {
            return [
                'ID' => $resident->id,
                'Nombre' => $resident->name,
                'Unidad' => $resident->unit->full_identifier,
                'Edad' => $resident->age ?? 'N/A',
                'Tiene cuenta' => $resident->auth_user_id ? '✓ Sí' : 'No',
                'Email' => $resident->authUser?->email ?? '-',
            ];
        });

        $this->table(
            ['ID', 'Nombre', 'Unidad', 'Edad', 'Tiene cuenta', 'Email'],
            $data
        );

        $this->newLine();
        $this->info('Total: '.$residents->count().' residentes');

        return 0;
    }
}
