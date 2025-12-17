<?php

namespace App\Console\Commands;

use App\Models\Building;
use App\Models\Complex;
use App\Models\Unit;
use App\Models\UnitUser;
use App\Models\User;
use App\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportUnitsFromIdataText extends Command
{
    /**
     * Signature:
     *  - You must provide a TXT file that contains the table lines (U.F. DEPTO. PROPIETARIO ...)
     */
    protected $signature = 'units:import-idata-text
        {path : Path to a TXT file that contains the IDATA table (pages 10+)}
        {--complex=El Talar de Martínez : Complex name}
        {--create-users : Create owner users and assign them as owner+responsible}
        {--dry-run : Do not write changes}';

    protected $description = 'Import units (U.F., depto, owner) from IDATA liquidation text (copy/paste from PDF pages 10+)';

    public function handle(): int
    {
        $path = (string) $this->argument('path');

        if (! is_file($path)) {
            $this->error("Archivo no encontrado: {$path}");

            return Command::FAILURE;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            $this->error('No se pudo leer el archivo.');

            return Command::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $createUsers = (bool) $this->option('create-users');
        $complexName = (string) $this->option('complex');

        $complex = Complex::query()->firstOrCreate(['name' => $complexName]);

        $rows = $this->extractRows($content);

        if (empty($rows)) {
            $this->warn('No se detectaron filas. Asegurate de pegar la tabla que contiene: U.F. DEPTO. PROPIETARIO ...');

            return Command::SUCCESS;
        }

        $this->info('Filas detectadas: '.count($rows));

        $createdBuildings = 0;
        $createdUnits = 0;
        $updatedUnits = 0;
        $createdUsers = 0;
        $createdAssignments = 0;

        foreach ($rows as $row) {
            [$uf, $depto, $ownerName, $coef] = $row;

            [$tower, $apt] = $this->splitDepto($depto);
            if (! $tower || ! $apt) {
                $this->warn("No se pudo interpretar DEPTO '{$depto}' (UF {$uf}).");
                continue;
            }

            $buildingName = "Torre {$tower}";

            $building = Building::query()->firstOrCreate(
                ['complex_id' => $complex->id, 'name' => $buildingName],
                ['address' => $complex->address]
            );

            if ($building->wasRecentlyCreated) {
                $createdBuildings++;
            }

            // Create or update unit.
            $unit = Unit::query()->where('uf_code', $uf)->first();

            // Fallback: match by building + number
            if (! $unit) {
                $unit = Unit::query()
                    ->where('building_id', $building->id)
                    ->where('number', $apt)
                    ->first();
            }

            $isNew = false;
            if (! $unit) {
                $unit = new Unit();
                $isNew = true;
            }

            $unit->building_id = $building->id;
            $unit->number = $apt;
            $unit->uf_code = $uf;

            // If we got a coefficient (PORC. A), store it.
            if ($coef !== null) {
                $unit->coefficient = $coef;
            }

            if ($dryRun) {
                // no-op
            } else {
                $unit->save();
            }

            if ($isNew) {
                $createdUnits++;
            } else {
                $updatedUnits++;
            }

            if ($createUsers) {
                $email = "uf{$uf}@eltalardemartinez.local";
                $user = User::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $ownerName,
                        'password' => Hash::make('password'),
                        'role' => Role::Propietario,
                        'approved_at' => now(),
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $createdUsers++;
                } else {
                    // keep the name in sync if it's empty or placeholder
                    if ($user->name !== $ownerName && ! $dryRun) {
                        $user->update(['name' => $ownerName]);
                    }
                }

                // Assign user to unit as owner+responsible
                if (! $dryRun) {
                    $assignment = UnitUser::query()->updateOrCreate(
                        [
                            'unit_id' => $unit->id,
                            'user_id' => $user->id,
                            'ended_at' => null,
                        ],
                        [
                            'is_owner' => true,
                            'is_responsible' => true,
                            'started_at' => now()->toDateString(),
                            'notes' => 'Importado desde IDATA (UF '.$uf.')',
                        ]
                    );

                    if ($assignment->wasRecentlyCreated) {
                        $createdAssignments++;
                    }
                }
            }
        }

        $this->info("Edificios creados: {$createdBuildings}");
        $this->info("Unidades creadas: {$createdUnits}");
        $this->info("Unidades actualizadas: {$updatedUnits}");

        if ($createUsers) {
            $this->info("Usuarios creados: {$createdUsers}");
            $this->info("Asignaciones creadas: {$createdAssignments}");
        }

        if ($dryRun) {
            $this->warn('DRY RUN: no se guardó nada.');
        }

        return Command::SUCCESS;
    }

    /**
     * Extract rows from raw text.
     * Returns array of [uf_code, depto, owner_name, coefficient|null]
     */
    protected function extractRows(string $content): array
    {
        $rows = [];

        foreach (preg_split('/\R/u', $content) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            // Typical line:
            // 0030 1102 DI LASCIO MATIAS 464579,30 -371663,30 ... 0,29% ...
            if (! preg_match('/^(\d{4})\s+(\d{2,4})\s+(.+?)\s+[-\d]/u', $line, $m)) {
                continue;
            }

            $uf = $m[1];
            $depto = $m[2];
            $owner = $this->cleanOwnerName($m[3]);

            $coef = null;
            if (preg_match('/(\d{1,2},\d{2})%/u', $line, $pm)) {
                // 0,29% => 0.0029
                $pct = (float) str_replace(',', '.', $pm[1]);
                $coef = round($pct / 100, 4);
            }

            $rows[] = [$uf, $depto, $owner, $coef];
        }

        return $rows;
    }

    protected function cleanOwnerName(string $raw): string
    {
        $name = preg_replace('/\s+/u', ' ', trim($raw)) ?? trim($raw);
        $name = rtrim($name, '.');
        $name = preg_replace('/\.\.\.+$/u', '', $name) ?? $name;

        // Keep as-is but normalize casing a bit if it is ALL CAPS.
        if (mb_strtoupper($name, 'UTF-8') === $name) {
            $name = Str::title(mb_strtolower($name, 'UTF-8'));
        }

        return trim($name);
    }

    /**
     * Split depto into [tower, apt]
     * Rules:
     * - last 2 digits are apt number
     * - prefix is tower number (1..99)
     */
    protected function splitDepto(string $depto): array
    {
        $depto = preg_replace('/\D+/', '', $depto) ?? $depto;

        if (strlen($depto) < 3) {
            return [null, null];
        }

        $apt = substr($depto, -2);
        $tower = substr($depto, 0, -2);

        $towerInt = (int) $tower;
        if ($towerInt <= 0) {
            return [null, null];
        }

        return [(string) $towerInt, str_pad((string) ((int) $apt), 2, '0', STR_PAD_LEFT)];
    }
}
