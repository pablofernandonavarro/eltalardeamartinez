<?php

namespace Database\Seeders;

use App\Models\SystemRule;
use Illuminate\Database\Seeder;

class ResidentsPerRoomRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear regla de límite de residentes por ambiente
        SystemRule::updateOrCreate(
            ['type' => 'unit_occupancy'],
            [
                'name' => 'Límite de Residentes por Ambiente',
                'description' => 'Define el máximo número de residentes permitidos por cada ambiente de una unidad funcional.',
                'conditions' => [
                    'applies_to' => 'all_units',
                ],
                'limits' => [
                    'max_residents_by_rooms' => [
                        1 => 2,   // 1 ambiente = 2 residentes
                        2 => 3,   // 2 ambientes = 3 residentes
                        3 => 4,   // 3 ambientes = 4 residentes
                        4 => 5,   // 4 ambientes = 5 residentes (máximo)
                        5 => 5,   // 5 ambientes = 5 residentes (máximo)
                        6 => 5,   // 6 ambientes = 5 residentes (máximo)
                        7 => 5,   // 7 ambientes = 5 residentes (máximo)
                        8 => 5,   // 8 ambientes = 5 residentes (máximo)
                        9 => 5,   // 9 ambientes = 5 residentes (máximo)
                        10 => 5,  // 10 ambientes = 5 residentes (máximo)
                    ],
                ],
                'is_active' => true,
                'valid_from' => now(),
                'valid_to' => null,
                'priority' => 10,
                'notes' => 'Esta regla se aplica automáticamente al agregar residentes. Por ejemplo, una unidad de 3 ambientes puede tener máximo 6 residentes (3 x 2).',
            ]
        );
    }
}
