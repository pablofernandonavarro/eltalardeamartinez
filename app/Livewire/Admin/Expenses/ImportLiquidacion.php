<?php

namespace App\Livewire\Admin\Expenses;

use App\Models\Building;
use App\Models\Expense;
use App\Models\Unit;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\Process\Process;

class ImportLiquidacion extends Component
{
    use WithFileUploads;

    public $pdf = null;

    public bool $importUnits = false;

    public bool $importExpenses = true;

    public ?array $preview = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public bool $importing = false;

    protected function rules(): array
    {
        return [
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ];
    }

    public function mount(): void
    {
        // Si no hay unidades cargadas, sugerir importarlas también
        $this->importUnits = Unit::query()->count() === 0;
    }

    public function updatedPdf(): void
    {
        $this->reset(['preview', 'errorMessage', 'successMessage']);
    }

    public function previsualizar(): void
    {
        $this->validate();
        $this->reset(['preview', 'errorMessage', 'successMessage']);

        try {
            $tmpPath = $this->pdf->getRealPath();
            $data = $this->runPythonExtractor($tmpPath);

            if ($data === null) {
                return;
            }

            $period = '';
            if ($data['period_year'] && $data['period_month']) {
                $period = sprintf('%04d-%02d', $data['period_year'], $data['period_month']);
            }

            $existingExpenses = 0;
            if ($period) {
                $existingExpenses = Expense::query()->where('period', $period)->count();
            }

            $validacion = $this->validarConsistencia($data['units']);

            $this->preview = [
                'period'             => $data['period'],
                'period_formatted'   => $period,
                'unit_count'         => count($data['units']),
                'total_gastos'       => $data['total_gastos'],
                'existing_expenses'  => $existingExpenses,
                'rubros'             => $data['rubros'],
                'validacion'         => $validacion,
            ];
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al procesar el PDF: '.$e->getMessage();
        }
    }

    public function importar(): void
    {
        $this->validate();

        if ($this->preview === null) {
            $this->errorMessage = 'Primero debe previsualizar el archivo.';

            return;
        }

        $this->reset(['errorMessage', 'successMessage']);
        $this->importing = true;

        try {
            $tmpPath = $this->pdf->store('livewire-tmp');
            $fullPath = storage_path('app/'.$tmpPath);

            $options = [
                '--pdf' => $fullPath,
            ];

            if ($this->importUnits) {
                $options['--first-import'] = true;
            }

            if (! $this->importExpenses) {
                $options['--no-expenses'] = true;
            }

            Artisan::call('liquidacion:import', $options);

            $output = Artisan::output();

            $this->successMessage = 'Importación completada.'.PHP_EOL.$output;
            $this->reset(['pdf', 'preview']);

            // Limpiar archivo temporal
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error durante la importación: '.$e->getMessage();
        } finally {
            $this->importing = false;
        }
    }

    /**
     * Controles de consistencia sobre las unidades del PDF.
     */
    private function validarConsistencia(array $units): array
    {
        $sinMatchBd       = [];  // en PDF pero sin registro en BD
        $sinMontoPdf      = [];  // monto 0 o negativo
        $coeficientesPorTorre = [];  // suma de coeficientes por torre
        $sinEnPdf         = [];  // en BD pero ausentes del PDF

        // Indexar el PDF por uf_code para búsqueda rápida
        $pdfPorUf = [];
        foreach ($units as $row) {
            $pdfPorUf[$row['uf']] = $row;
        }

        // Cargar unidades de BD indexadas por uf_code
        $dbUnits = Unit::query()
            ->with('building')
            ->whereIn('uf_code', array_keys($pdfPorUf))
            ->get()
            ->keyBy('uf_code');

        // Agrupar unidades del PDF por torre para suma de coeficientes
        $pdfPorTorre = [];
        foreach ($units as $row) {
            $pdfPorTorre[$row['building']][] = $row;
        }

        foreach ($units as $row) {
            // 1. Sin match en BD
            if (! isset($dbUnits[$row['uf']])) {
                $sinMatchBd[] = [
                    'uf'       => $row['uf'],
                    'depto'    => $row['depto'],
                    'torre'    => $row['building'],
                    'monto'    => $row['gastos_a'],
                    'owner'    => $row['owner'],
                ];
            }

            // 2. Monto cero o negativo
            if ($row['gastos_a'] <= 0) {
                $sinMontoPdf[] = [
                    'uf'    => $row['uf'],
                    'depto' => $row['depto'],
                    'torre' => $row['building'],
                    'monto' => $row['gastos_a'],
                ];
            }
        }

        // 3. Suma global de coeficientes (debe ser ~1.0 en todo el complejo)
        $sumaGlobal = round(array_sum(array_column($units, 'coefficient')), 6);
        $coeficientesPorTorre = [
            [
                'suma'       => $sumaGlobal,
                'diferencia' => round(abs(1.0 - $sumaGlobal), 6),
                'ok'         => $sumaGlobal >= 0.98 && $sumaGlobal <= 1.02,
            ],
        ];

        // 4. Unidades en BD que no están en el PDF (por torre si está registrada)
        $torresEnPdf = array_unique(array_column($units, 'building'));
        $buildings = Building::query()->whereIn('name', $torresEnPdf)->with('units')->get();

        foreach ($buildings as $building) {
            foreach ($building->units as $unit) {
                if ($unit->uf_code && ! isset($pdfPorUf[$unit->uf_code])) {
                    $sinEnPdf[] = [
                        'uf'    => $unit->uf_code,
                        'depto' => $unit->number,
                        'torre' => $building->name,
                    ];
                }
            }
        }

        return [
            'sin_match_bd'          => $sinMatchBd,
            'sin_monto'             => $sinMontoPdf,
            'coeficientes_por_torre' => $coeficientesPorTorre,
            'sin_en_pdf'            => $sinEnPdf,
            'tiene_alertas'         => ! empty($sinMatchBd) || ! empty($sinMontoPdf) || ! empty($sinEnPdf)
                || collect($coeficientesPorTorre)->contains(fn ($c) => ! $c['ok']),
        ];
    }

    public function render()
    {
        return view('livewire.admin.expenses.import-liquidacion')
            ->layout('components.layouts.app', ['title' => 'Importar Liquidación']);
    }

    private function runPythonExtractor(string $pdfPath): ?array
    {
        $scriptPath = base_path('scripts/extract_liquidacion.py');

        $candidates = $this->pythonCandidates();
        $lastError = '';

        foreach ($candidates as $cmd) {
            $process = new Process([$cmd, $scriptPath, $pdfPath]);
            $process->setTimeout(120);
            $process->setEnv(['PYTHONIOENCODING' => 'utf-8', 'PYTHONUTF8' => '1']);

            try {
                $process->run();
            } catch (\Throwable) {
                continue;
            }

            if ($process->isSuccessful()) {
                $output = trim($process->getOutput());
                $output = ltrim($output, "\xEF\xBB\xBF");
                $data = json_decode($output, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->errorMessage = 'El script no retornó JSON válido ('.json_last_error_msg().'): '.substr($output, 0, 200);

                    return null;
                }

                if (isset($data['error'])) {
                    $this->errorMessage = 'Error del script: '.$data['error'];

                    return null;
                }

                return $data;
            }

            $lastError = trim($process->getErrorOutput()) ?: 'exit '.$process->getExitCode();
        }

        $tried = implode(', ', $candidates);
        $this->errorMessage = "Python no encontrado (probados: {$tried}). "
            ."Agregar PYTHON_PATH=C:\\Python314\\python.exe en el .env — último error: {$lastError}";

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
