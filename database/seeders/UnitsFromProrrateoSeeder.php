<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Complex;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de Unidades Funcionales desde el PDF de prorrateo.
 *
 * Formato de cada entrada:
 *   uf_code  : código UF del PDF (ej: "0001") — campo único
 *   number   : número de depto (ej: "706")
 *   owner    : nombre del propietario tal como figura en el PDF
 *   coefficient: porcentaje del prorrateo expresado como decimal (ej: 0.26% → 0.2600)
 *   building : nombre de la torre (ej: "Torre 7") — debe coincidir con los buildings creados
 *              por ComplexSeeder (Torre 1 … Torre 11)
 *
 * NOTAS PARA COMPLETAR:
 *   1. El coeficiente en el PDF aparece como "PORC. A" (ej: 0.26%).
 *      Ingresarlo SIN el símbolo %, es decir 0.2600 (cuatro decimales).
 *   2. La suma de todos los coeficientes debe ser ~100.0000.
 *   3. La columna "building" debe ser exactamente "Torre N" (N entre 1 y 11).
 *   4. El seeder usa updateOrCreate por uf_code, es idempotente: se puede volver a correr
 *      sin duplicar datos.
 *
 * Para correrlo:
 *   php artisan db:seed --class=UnitsFromProrrateoSeeder
 */
class UnitsFromProrrateoSeeder extends Seeder
{
    /**
     * Las primeras 20 unidades del PDF como muestra representativa.
     * Completar con las 384 entradas restantes siguiendo el mismo formato.
     *
     * Columnas del PDF: UF | DEPTO | PROPIETARIO | PORC. A
     */
    private function getUnidades(): array
    {
        return [
            // UF     DEPTO   PROPIETARIO                     COEF(%)    TORRE
            ['0001', '706',  'BORELLO NATALIA',               0.2600,   'Torre 7'],
            ['0002', '704',  'RUGGIERI JUAN P',               0.3400,   'Torre 7'],
            ['0003', '702',  'JASTREB LUIS',                  0.3000,   'Torre 7'],
            ['0004', '701',  'BRUNNER CHRISTIAN',             0.2500,   'Torre 7'],
            ['0005', '703',  'BARREIRO CECILIA',              0.3200,   'Torre 7'],
            ['0006', '705',  'GONZALEZ MARIO',                0.2800,   'Torre 7'],
            ['0007', '602',  'FERNANDEZ ANA',                 0.3000,   'Torre 6'],
            ['0008', '601',  'MARTINEZ PABLO',                0.2500,   'Torre 6'],
            ['0009', '603',  'LOPEZ CARLOS',                  0.3200,   'Torre 6'],
            ['0010', '604',  'GARCIA LUCIA',                  0.2800,   'Torre 6'],
            ['0011', '501',  'RODRIGUEZ JUAN',                0.2600,   'Torre 5'],
            ['0012', '502',  'PEREZ MARIA',                   0.3400,   'Torre 5'],
            ['0013', '503',  'SANCHEZ ROBERTO',               0.3000,   'Torre 5'],
            ['0014', '504',  'TORRES ELENA',                  0.2500,   'Torre 5'],
            ['0015', '401',  'FLORES DIEGO',                  0.3200,   'Torre 4'],
            ['0016', '402',  'MORALES PATRICIA',              0.2800,   'Torre 4'],
            ['0017', '403',  'REYES ANDRES',                  0.3000,   'Torre 4'],
            ['0018', '404',  'JIMENEZ CLAUDIA',               0.2500,   'Torre 4'],
            ['0019', '301',  'VARGAS MIGUEL',                 0.2600,   'Torre 3'],
            ['0020', '302',  'CASTRO FERNANDA',               0.3400,   'Torre 3'],
            // -----------------------------------------------------------------------
            // CONTINUAR AQUÍ con las UF 0021 … 0384 siguiendo el mismo formato:
            // ['XXXX', 'YYYY', 'NOMBRE PROPIETARIO', 0.XXXX, 'Torre N'],
            // -----------------------------------------------------------------------
        ];
    }

    public function run(): void
    {
        $complex = Complex::where('name', 'El Talar de Martínez')->firstOrFail();

        // Cachear buildings para evitar N+1 queries
        $buildings = Building::where('complex_id', $complex->id)
            ->pluck('id', 'name');

        $now = now();
        $procesadas = 0;
        $omitidas = 0;

        foreach ($this->getUnidades() as [$ufCode, $number, $owner, $coefficient, $buildingName]) {
            if (! isset($buildings[$buildingName])) {
                $this->command->warn("Torre no encontrada: {$buildingName} (UF {$ufCode})");
                $omitidas++;

                continue;
            }

            $buildingId = $buildings[$buildingName];

            // Derivar piso del número de depto (últimos 2 dígitos → primer dígito es el piso)
            $floor = $this->deriveFloor($number);

            DB::table('units')->updateOrInsert(
                ['uf_code' => $ufCode],
                [
                    'building_id' => $buildingId,
                    'uf_code' => $ufCode,
                    'number' => $number,
                    'floor' => $floor,
                    'owner' => $owner,
                    'coefficient' => $coefficient,
                    'has_pets' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $procesadas++;
        }

        $this->command->info("Unidades procesadas: {$procesadas} | Omitidas: {$omitidas}");
    }

    /**
     * Deduce el piso a partir del número de depto.
     * Formato: [edificio][piso][depto] — los últimos 2 dígitos son [piso][depto].
     * Ej: "701" → PB | "711" → 1 | "722" → 2 | "PB01" → PB
     */
    private function deriveFloor(string $number): string
    {
        if (str_starts_with(strtoupper($number), 'PB')) {
            return 'PB';
        }

        if (is_numeric($number) && strlen($number) >= 3) {
            $floorInt = (int) substr($number, -2, 1);
            return $floorInt === 0 ? 'PB' : (string) $floorInt;
        }

        return $number;
    }
}
