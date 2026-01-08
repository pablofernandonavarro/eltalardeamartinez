<?php

namespace App\Console\Commands;

use App\Models\PoolEntry;
use Illuminate\Console\Command;

class CloseOpenPoolEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pools:close-open-entries {--before= : Close entries entered before this date (Y-m-d). Default: today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close (checkout) pool entries that are still open from previous days';

    public function handle(): int
    {
        $before = $this->option('before');
        $cutoff = $before
            ? now()->parse($before)->startOfDay()
            : now()->startOfDay();
        
        // NUNCA cerrar entradas de hoy - solo de días anteriores
        if ($cutoff->isToday()) {
            $this->info('No se cierran entradas del día actual. Solo de días anteriores.');
            return Command::SUCCESS;
        }

        $query = PoolEntry::query()
            ->whereNull('exited_at')
            ->where('entered_at', '<', $cutoff);

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('No hay ingresos abiertos para cerrar.');

            return Command::SUCCESS;
        }

        $this->info("Cerrando {$count} ingresos abiertos (anteriores a {$cutoff->toDateString()})...");

        $closed = 0;

        $query->orderBy('id')->chunkById(200, function ($entries) use (&$closed) {
            foreach ($entries as $entry) {
                /** @var PoolEntry $entry */
                $entry->update([
                    'exited_at' => $entry->entered_at ? $entry->entered_at->copy()->endOfDay() : now()->subSecond(),
                    'exited_by_user_id' => null,
                    'exit_notes' => 'Cierre automático (fin de día).',
                ]);

                $closed++;
            }
        });

        $this->info("Listo. Cerrados: {$closed}.");

        return Command::SUCCESS;
    }
}
