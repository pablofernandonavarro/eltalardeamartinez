<?php

namespace Database\Seeders;

use App\Models\Concept;
use Illuminate\Database\Seeder;

class ConceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $concepts = [
            [
                'name' => 'Mantenimiento General',
                'description' => 'Gastos de mantenimiento general del complejo',
                'is_active' => true,
            ],
            [
                'name' => 'Limpieza',
                'description' => 'Servicios de limpieza y conserjería',
                'is_active' => true,
            ],
            [
                'name' => 'Seguridad',
                'description' => 'Servicios de seguridad y vigilancia',
                'is_active' => true,
            ],
            [
                'name' => 'Servicios Públicos',
                'description' => 'Luz, agua, gas común',
                'is_active' => true,
            ],
            [
                'name' => 'Reparación Pileta',
                'description' => 'Reparación y mantenimiento de piletas',
                'is_active' => true,
            ],
            [
                'name' => 'Pintura',
                'description' => 'Pintura de fachadas y espacios comunes',
                'is_active' => true,
            ],
            [
                'name' => 'Ascensor',
                'description' => 'Mantenimiento y reparación de ascensores',
                'is_active' => true,
            ],
        ];

        foreach ($concepts as $concept) {
            Concept::create($concept);
        }
    }
}
