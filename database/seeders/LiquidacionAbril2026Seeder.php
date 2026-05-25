<?php

namespace Database\Seeders;

use App\ExpenseStatus;
use App\ExpenseType;
use App\Models\Building;
use App\Models\Complex;
use App\Models\Concept;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de Liquidación de Expensas — Abril 2026
 *
 * Genera:
 *   1. Un registro Concept "Expensas Ordinarias"
 *   2. Un Expense por building con el total proporcional del mes
 *   3. Un ExpenseDetail por unidad con:
 *      - amount    : GASTOS A (cuota del mes, calculada por coeficiente)
 *      - metadata  : {
 *            previous_balance : S.ANTERIOR del PDF (saldo anterior del período)
 *            payments_period  : PAGOS (pagos realizados en el período)
 *            bonification     : BONIFIC
 *            accumulated_debt : DEUDA (deuda acumulada previa)
 *            interests        : INTERESES
 *            total            : TOTAL (lo que realmente debe pagar)
 *            rubros           : desglose de los 10 rubros (clave => monto)
 *        }
 *
 * USO:
 *   php artisan db:seed --class=LiquidacionAbril2026Seeder
 *
 * PREREQUISITO: Correr UnitsFromProrrateoSeeder primero para tener las unidades cargadas.
 *
 * DATOS ADICIONALES POR UNIDAD (DEUDORAS):
 *   El array $deudoras contiene las unidades con saldo pendiente.
 *   Completar con los datos del PDF para reflejar el estado real de cuenta.
 */
class LiquidacionAbril2026Seeder extends Seeder
{
    // -------------------------------------------------------------------------
    // TOTALES DEL MES — extraídos del PDF
    // -------------------------------------------------------------------------
    private const PERIOD = '2026-04';

    private const DUE_DATE = '2026-04-10';

    private const TOTAL_LIQUIDACION = 152_210_689.68;

    /**
     * Los 10 rubros del período con sus montos totales del complejo.
     * Estos se guardan en metadata del Expense (nivel complejo)
     * y también en el metadata de cada ExpenseDetail (proporcional).
     */
    private function getRubros(): array
    {
        return [
            'rubro_01_remuneraciones' => 3_532_522.29,
            'rubro_02_servicios_publicos' => 12_069_944.83,
            'rubro_03_abonos_servicios' => 67_506_588.46,
            'rubro_04_mantenimiento_comun' => 23_288_205.86,
            'rubro_05_reparaciones_uf' => 13_762_209.66,
            'rubro_06_gastos_bancarios' => 1_975_069.08,
            'rubro_07_gastos_limpieza' => 1_057_135.00,
            'rubro_08_gastos_administracion' => 15_493_583.00,
            'rubro_09_seguros' => 3_208_559.19,
            'rubro_10_otros' => 10_316_872.31,
        ];
    }

    /**
     * Unidades deudoras con su estado de cuenta completo del PDF.
     * Clave: uf_code | Valor: array con los campos del estado de cuenta.
     *
     * COMPLETAR con los 30 deudores reales del PDF.
     * Formato:
     *   's_anterior'      => importe del saldo anterior
     *   'pagos'           => pagos realizados en el período
     *   'bonificacion'    => bonificación aplicada
     *   'deuda'           => deuda acumulada
     *   'intereses'       => intereses calculados
     *   // 'gastos_a' se calcula automáticamente por coeficiente
     *   'total'           => TOTAL a pagar según PDF
     */
    private function getDeudoras(): array
    {
        return [
            // Ejemplo de unidades deudoras — completar con los datos reales del PDF
            // '0001' => ['s_anterior' => 150000.00, 'pagos' => 0.00, 'bonificacion' => 0.00, 'deuda' => 150000.00, 'intereses' => 7500.00, 'total' => 557834.21],
            // '0002' => ['s_anterior' => 80000.00,  'pagos' => 0.00, 'bonificacion' => 0.00, 'deuda' => 80000.00,  'intereses' => 4000.00, 'total' => 491234.56],
            // -----------------------------------------------------------------------
            // COMPLETAR CON LOS 30 DEUDORES DEL PDF
            // -----------------------------------------------------------------------
        ];
    }

    public function run(): void
    {
        $complex = Complex::where('name', 'El Talar de Martínez')->firstOrFail();

        // Obtener o crear el concepto "Expensas Ordinarias"
        $concept = Concept::firstOrCreate(
            ['name' => 'Expensas Ordinarias'],
            ['description' => 'Liquidación mensual de expensas del consorcio', 'is_active' => true]
        );

        // Obtener todos los buildings del complejo
        $buildings = Building::where('complex_id', $complex->id)->get();

        if ($buildings->isEmpty()) {
            $this->command->error('No se encontraron edificios. Correr ComplexSeeder primero.');

            return;
        }

        // Calcular el coeficiente total de todas las unidades para el prorrateo correcto
        $totalCoefficient = Unit::whereIn('building_id', $buildings->pluck('id'))
            ->whereNull('deleted_at')
            ->sum('coefficient');

        if ($totalCoefficient == 0) {
            $this->command->error('Coeficiente total = 0. Correr UnitsFromProrrateoSeeder primero.');

            return;
        }

        $this->command->info("Coeficiente total: {$totalCoefficient}");
        $this->command->info('Total liquidación: $'.number_format(self::TOTAL_LIQUIDACION, 2));

        $rubros = $this->getRubros();
        $deudoras = $this->getDeudoras();
        $detallesCreados = 0;

        DB::transaction(function () use (
            $buildings, $concept, $rubros, $deudoras, $totalCoefficient, &$detallesCreados
        ) {
            foreach ($buildings as $building) {
                $units = Unit::where('building_id', $building->id)
                    ->whereNull('deleted_at')
                    ->get();

                if ($units->isEmpty()) {
                    continue;
                }

                // Calcular el monto del building proporcional a sus unidades
                $buildingCoefficient = $units->sum('coefficient');
                $buildingAmount = round((self::TOTAL_LIQUIDACION * $buildingCoefficient) / $totalCoefficient, 2);

                // Eliminar expense preexistente del mismo período para este building (idempotente)
                Expense::where('building_id', $building->id)
                    ->where('period', self::PERIOD)
                    ->withTrashed()
                    ->get()
                    ->each(function ($e) {
                        $e->details()->withTrashed()->forceDelete();
                        $e->forceDelete();
                    });

                // Crear el Expense para este building
                $expense = Expense::create([
                    'building_id' => $building->id,
                    'concept_id' => $concept->id,
                    'type' => ExpenseType::Ordinaria,
                    'period' => self::PERIOD,
                    'due_date' => self::DUE_DATE,
                    'total_amount' => $buildingAmount,
                    'description' => 'Liquidación de Expensas - Abril 2026',
                    'metadata' => [
                        'source' => 'PDF prorrateo Abril 2026',
                        'total_complejo' => self::TOTAL_LIQUIDACION,
                        'coeficiente_edificio' => $buildingCoefficient,
                        'rubros' => $rubros,
                        'rubros_labels' => $this->getRubrosLabels(),
                    ],
                ]);

                // Crear un ExpenseDetail por unidad
                $details = [];
                foreach ($units as $unit) {
                    // Calcular GASTOS A por coeficiente
                    $gastosA = round((self::TOTAL_LIQUIDACION * $unit->coefficient) / $totalCoefficient, 2);

                    // Calcular rubros proporcionales para esta unidad
                    $rubrosUF = [];
                    foreach ($rubros as $key => $montoTotal) {
                        $rubrosUF[$key] = round(($montoTotal * $unit->coefficient) / $totalCoefficient, 2);
                    }

                    // Datos del estado de cuenta del PDF (deudoras o unidad al día)
                    $estadoCuenta = $deudoras[$unit->uf_code] ?? [
                        's_anterior' => 0.00,
                        'pagos' => 0.00,
                        'bonificacion' => 0.00,
                        'deuda' => 0.00,
                        'intereses' => 0.00,
                        'total' => $gastosA,
                    ];

                    // El 'total' del PDF es lo que realmente debe pagar (incluye deuda + intereses)
                    $totalAPagar = $estadoCuenta['total'];

                    $details[] = [
                        'expense_id' => $expense->id,
                        'unit_id' => $unit->id,
                        'amount' => $gastosA,        // cuota del mes
                        'paid_amount' => 0.00,
                        'status' => ExpenseStatus::Pendiente->value,
                        'notes' => null,
                        'metadata' => json_encode([
                            'previous_balance' => $estadoCuenta['s_anterior'],
                            'payments_period' => $estadoCuenta['pagos'],
                            'bonification' => $estadoCuenta['bonificacion'],
                            'accumulated_debt' => $estadoCuenta['deuda'],
                            'interests' => $estadoCuenta['intereses'],
                            'total_to_pay' => $totalAPagar,
                            'rubros' => $rubrosUF,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Insert en lote para eficiencia
                foreach (array_chunk($details, 100) as $chunk) {
                    ExpenseDetail::insert($chunk);
                }

                $detallesCreados += count($details);
                $this->command->line("  {$building->name}: {$units->count()} unidades — \$".number_format($buildingAmount, 2));
            }
        });

        $this->command->info("Liquidacion Abril 2026 creada. Detalles por unidad: {$detallesCreados}");
        $this->command->warn('Recordar completar el array getDeudoras() con los 30 deudores del PDF.');
    }

    /**
     * Labels legibles de cada rubro para mostrar en la UI.
     */
    private function getRubrosLabels(): array
    {
        return [
            'rubro_01_remuneraciones' => 'Remuneraciones al Personal',
            'rubro_02_servicios_publicos' => 'Servicios Públicos',
            'rubro_03_abonos_servicios' => 'Abonos de Servicios',
            'rubro_04_mantenimiento_comun' => 'Mantenimiento Partes Comunes',
            'rubro_05_reparaciones_uf' => 'Reparaciones en Unidades',
            'rubro_06_gastos_bancarios' => 'Gastos Bancarios',
            'rubro_07_gastos_limpieza' => 'Gastos de Limpieza',
            'rubro_08_gastos_administracion' => 'Gastos de Administración',
            'rubro_09_seguros' => 'Seguros',
            'rubro_10_otros' => 'Otros',
        ];
    }
}
