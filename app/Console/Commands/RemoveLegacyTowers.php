<?php

namespace App\Console\Commands;

use App\Models\Building;
use App\Models\Complex;
use Illuminate\Console\Command;

class RemoveLegacyTowers extends Command
{
    protected $signature = 'buildings:remove-legacy-towers
        {--complex=El Talar de Martínez : Complex name}
        {--force : Actually delete the buildings (otherwise dry-run)}';

    protected $description = 'Remove legacy buildings named "Torre A" and "Torre B" (and their units) created by old seeders';

    public function handle(): int
    {
        $complexName = (string) $this->option('complex');
        $force = (bool) $this->option('force');

        $complex = Complex::query()->where('name', $complexName)->first();

        if (! $complex) {
            $this->warn("No se encontró el complejo '{$complexName}'.");

            return Command::SUCCESS;
        }

        $query = Building::query()
            ->where('complex_id', $complex->id)
            ->whereIn('name', ['Torre A', 'Torre B']);

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('No hay Torre A / Torre B para eliminar.');

            return Command::SUCCESS;
        }

        $this->warn("Se encontraron {$count} edificios para eliminar: ");
        foreach ((clone $query)->orderBy('name')->get() as $b) {
            $this->line("- {$b->name} (id={$b->id})");
        }

        if (! $force) {
            $this->warn('Dry-run: ejecutá con --force para eliminar.');

            return Command::SUCCESS;
        }

        $deleted = 0;
        foreach ($query->get() as $b) {
            $b->delete();
            $deleted++;
        }

        $this->info("Listo. Eliminados: {$deleted}. (Las unidades asociadas se eliminan por cascade.)");

        return Command::SUCCESS;
    }
}
