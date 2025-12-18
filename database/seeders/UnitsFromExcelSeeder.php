<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class UnitsFromExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/seeders/datos_talar.xlsx');

        $rows = Excel::toArray([], $path)[0]; // primera hoja

        $now = now();

        foreach ($rows as $index => $row) {

            // saltear encabezado si existiera
            if ($index === 0 && !is_numeric($row[1])) {
                continue;
            }

            $number = trim((string)$row[1]); // columna unidad
            $name   = trim((string)$row[2]); // columna nombre

            if (!$number) {
                continue;
            }

            // ---- lógica de deducción ----
            $lastTwo   = substr($number, -2);
            $building  = intval(substr($number, 0, -2));
            $floorNum  = intval(substr($lastTwo, 0, 1));
            $dept      = intval(substr($lastTwo, 1, 1));

            $floor = $floorNum === 0 ? 'PB' : (string)$floorNum;

            // evitar duplicados
            $exists = DB::table('units')
                ->where('building_id', $building)
                ->where('number', $number)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('units')->insert([
                'building_id' => $building,
                'number' => $number,
                'floor' => $floor,
                'owner' => $name ,
                'coefficient' => 1.0000,
                'has_pets' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
