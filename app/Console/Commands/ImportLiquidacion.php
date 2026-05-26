<?php

namespace App\Console\Commands;

use App\ExpenseStatus;
use App\ExpenseType;
use App\Models\Building;
use App\Models\Complex;
use App\Models\Concept;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Unit;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ImportLiquidacion extends Command
{
    protected $signature = 'liquidacion:import
        {--pdf= : Ruta al archivo PDF de liquidación}
        {--first-import : Crear edificios y unidades si no existen}
        {--dry-run : Mostrar qué haría sin modificar la base de datos}
        {--no-units : No crear ni actualizar unidades}
        {--no-expenses : No crear expenses ni expense details}';

    protected $description = 'Importa la liquidación mensual de expensas desde un PDF iData/MisExpensas';

    public function handle(): int
    {
        $pdfPath = (string) $this->option('pdf');

        if (! $pdfPath || ! is_file($pdfPath)) {
            $this->error('Debe indicar una ruta válida con --pdf=/path/to/file.pdf');

            return Command::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $firstImport = (bool) $this->option('first-import');
        $skipUnits = (bool) $this->option('no-units');
        $skipExpenses = (bool) $this->option('no-expenses');

        $this->info("Procesando: {$pdfPath}");

        $data = $this->runPythonExtractor($pdfPath);
        if ($data === null) {
            return Command::FAILURE;
        }

        if (empty($data['units'])) {
            $this->warn('No se detectaron unidades en el PDF. Verificar el archivo.');

            return Command::FAILURE;
        }

        $this->info("Período: {$data['period']} ({$data['period_month']}/{$data['period_year']})");
        $this->info('Unidades detectadas: '.count($data['units']));

        if ($dryRun) {
            $this->warn('DRY RUN activo — no se guardará nada.');
        }

        $createdBuildings = 0;
        $createdUnits = 0;
        $updatedUnits = 0;
        $createdExpenses = 0;
        $skippedExpenses = 0;
        $createdDetails = 0;

        // Unidades agrupadas por edificio para crear Expenses por torre
        $unitsByBuilding = [];

        if (! $skipUnits) {
            $complex = Complex::query()->first();
            if (! $complex) {
                $this->error('No existe ningún Complex en la base de datos. Crear uno primero.');

                return Command::FAILURE;
            }

            foreach ($data['units'] as $row) {
                $buildingName = $row['building'];

                $building = Building::query()->firstOrCreate(
                    ['complex_id' => $complex->id, 'name' => $buildingName],
                    ['address' => $complex->address ?? '']
                );

                if ($building->wasRecentlyCreated) {
                    $createdBuildings++;
                }

                $unit = Unit::query()->where('uf_code', $row['uf'])->first();

                if (! $unit) {
                    $unit = Unit::query()
                        ->where('building_id', $building->id)
                        ->where('number', $row['depto'])
                        ->first();
                }

                $isNew = ! $unit;
                if ($isNew) {
                    $unit = new Unit;
                }

                $unit->building_id = $building->id;
                $unit->number = $row['depto'];
                $unit->uf_code = $row['uf'];
                $unit->owner = $row['owner'];

                // Derivar piso: formato [edificio][piso][depto] → anteúltimo dígito es el piso
                if (! $unit->floor) {
                    $depto = (string) $row['depto'];
                    if (str_starts_with(strtoupper($depto), 'PB')) {
                        $unit->floor = 'PB';
                    } elseif (is_numeric($depto) && strlen($depto) >= 3) {
                        $floorInt = (int) substr($depto, -2, 1);
                        $unit->floor = $floorInt === 0 ? 'PB' : (string) $floorInt;
                    }
                }

                if ($row['coefficient'] > 0) {
                    $unit->coefficient = $row['coefficient'];
                }

                if (! $dryRun) {
                    $unit->save();
                }

                $isNew ? $createdUnits++ : $updatedUnits++;

                // Deduplicate by unit ID: keep last gastos_a if same unit appears twice in the PDF
                $unitsByBuilding[$buildingName][$unit->id ?? $row['uf']] = [
                    'unit' => $unit,
                    'gastos_a' => $row['gastos_a'],
                ];
            }
        } else {
            // Si se saltean unidades, cargar desde BD para armar el mapa por edificio
            foreach ($data['units'] as $row) {
                $unit = Unit::query()->where('uf_code', $row['uf'])->first();
                if ($unit) {
                    $unitsByBuilding[$row['building']][$unit->id] = [
                        'unit' => $unit,
                        'gastos_a' => $row['gastos_a'],
                    ];
                }
            }
        }

        if (! $skipExpenses && ! empty($data['period_year'])) {
            $period = sprintf('%04d-%02d', $data['period_year'], $data['period_month']);

            $concept = Concept::query()->firstOrCreate(
                ['name' => 'Expensas Ordinarias'],
                ['description' => 'Expensas ordinarias mensuales', 'is_active' => true]
            );

            foreach ($unitsByBuilding as $buildingName => $buildingUnits) {
                $building = Building::query()
                    ->whereHas('complex')
                    ->where('name', $buildingName)
                    ->first();

                if (! $building) {
                    $this->warn("Edificio no encontrado: {$buildingName}");

                    continue;
                }

                $expense = Expense::query()
                    ->where('period', $period)
                    ->where('building_id', $building->id)
                    ->first();

                if ($expense) {
                    // Expense ya existe — solo agregar detalles faltantes
                    $addedDetails = 0;
                    if (! $dryRun) {
                        foreach ($buildingUnits as $item) {
                            if ($item['gastos_a'] <= 0) {
                                continue;
                            }
                            // Guardia: la unidad debe pertenecer al mismo edificio que la expense
                            if ($item['unit']->building_id !== $building->id) {
                                $this->warn("Unidad {$item['unit']->number} (id={$item['unit']->id}) no pertenece a {$buildingName} — saltando.");
                                continue;
                            }
                            $detail = ExpenseDetail::firstOrCreate(
                                [
                                    'expense_id' => $expense->id,
                                    'unit_id'    => $item['unit']->id,
                                ],
                                [
                                    'amount'      => $item['gastos_a'],
                                    'paid_amount' => 0,
                                    'status'      => ExpenseStatus::Pendiente,
                                ]
                            );
                            if ($detail->wasRecentlyCreated) {
                                $addedDetails++;
                                $createdDetails++;
                            }
                        }
                        // Recalcular total si se agregaron detalles
                        if ($addedDetails > 0) {
                            $expense->total_amount = ExpenseDetail::where('expense_id', $expense->id)->sum('amount');
                            $expense->save();
                        }
                    }
                    $msg = $addedDetails > 0
                        ? "Expense existente {$buildingName} {$period}: {$addedDetails} detalles nuevos agregados."
                        : "Expense existente {$buildingName} {$period}: sin detalles nuevos.";
                    $this->line($msg);
                    $skippedExpenses++;

                    continue;
                }

                $totalGastosA = collect($buildingUnits)->sum('gastos_a');

                // Fecha de vencimiento: último día del mes del período
                $dueDate = \Carbon\Carbon::createFromDate($data['period_year'], $data['period_month'], 1)
                    ->endOfMonth()
                    ->toDateString();

                if (! $dryRun) {
                    $expense = Expense::create([
                        'building_id' => $building->id,
                        'concept_id' => $concept->id,
                        'type' => ExpenseType::Ordinaria,
                        'period' => $period,
                        'due_date' => $dueDate,
                        'total_amount' => $totalGastosA,
                        'description' => "Expensas {$data['period']} - {$buildingName}",
                    ]);

                    foreach ($buildingUnits as $item) {
                        if ($item['gastos_a'] <= 0) {
                            continue;
                        }

                        // Guardia: la unidad debe pertenecer al mismo edificio que la expense
                        if ($item['unit']->building_id !== $building->id) {
                            $this->warn("Unidad {$item['unit']->number} (id={$item['unit']->id}) no pertenece a {$buildingName} — saltando.");
                            continue;
                        }

                        $detail = ExpenseDetail::firstOrCreate(
                            [
                                'expense_id' => $expense->id,
                                'unit_id' => $item['unit']->id,
                            ],
                            [
                                'amount' => $item['gastos_a'],
                                'paid_amount' => 0,
                                'status' => ExpenseStatus::Pendiente,
                            ]
                        );

                        if ($detail->wasRecentlyCreated) {
                            $createdDetails++;
                        }
                    }
                }

                $createdExpenses++;
            }
        }

        $this->newLine();
        $this->info("Edificios creados:      {$createdBuildings}");
        $this->info("Unidades creadas:       {$createdUnits}");
        $this->info("Unidades actualizadas:  {$updatedUnits}");
        $this->info("Expenses creadas:       {$createdExpenses}");
        $this->info("Expenses salteadas:     {$skippedExpenses} (ya existían)");
        $this->info("Detalles creados:       {$createdDetails}");

        if ($dryRun) {
            $this->warn('DRY RUN: no se guardó nada.');
        }

        return Command::SUCCESS;
    }

    /**
     * Ejecuta el script Python y retorna el array decodificado, o null en caso de error.
     */
    private function runPythonExtractor(string $pdfPath): ?array
    {
        $scriptPath = base_path('scripts/extract_liquidacion.py');

        if (! is_file($scriptPath)) {
            $this->error("Script no encontrado: {$scriptPath}");

            return null;
        }

        $candidates = $this->pythonCandidates();
        $lastError = '';

        foreach ($candidates as $cmd) {
            $process = new Process([$cmd, $scriptPath, $pdfPath]);
            $process->setTimeout(120);
            $process->setEnv(['PYTHONIOENCODING' => 'utf-8', 'PYTHONUTF8' => '1']);

            try {
                $process->run();
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                continue;
            }

            if ($process->isSuccessful()) {
                $output = trim($process->getOutput());
                // Strip UTF-8 BOM if present
                $output = ltrim($output, "\xEF\xBB\xBF");
                $data = json_decode($output, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('El script Python no retornó JSON válido: '.json_last_error_msg());
                    $this->error(substr($output, 0, 500));

                    return null;
                }

                if (isset($data['error'])) {
                    $this->error('Error del script Python: '.$data['error']);

                    return null;
                }

                return $data;
            }

            $lastError = trim($process->getErrorOutput()) ?: 'exit code '.$process->getExitCode();
        }

        $this->error('No se pudo ejecutar Python. Comandos probados: '.implode(', ', $candidates));
        $this->error('Último error: '.$lastError);
        $this->line('Solución: agregar PYTHON_PATH=C:\\Python314\\python.exe en el .env');

        return null;
    }

    /** @return string[] */
    private function pythonCandidates(): array
    {
        $envPath = env('PYTHON_PATH');
        if ($envPath && trim($envPath) !== '') {
            return [trim($envPath, " \t\n\r\0\x0B\"'")];
        }

        return ['python3', 'python', 'python3.14', 'py'];
    }
}
