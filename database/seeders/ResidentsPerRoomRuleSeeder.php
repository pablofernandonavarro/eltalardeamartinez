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
                        2 => 4,   // 2 ambientes = 4 residentes
                        3 => 6,   // 3 ambientes = 6 residentes
                        4 => 8,   // 4 ambientes = 8 residentes
                        5 => 10,  // 5 ambientes = 10 residentes
                        6 => 12,  // 6 ambientes = 12 residentes
                        7 => 14,  // 7 ambientes = 14 residentes
                        8 => 16,  // 8 ambientes = 16 residentes
                        9 => 18,  // 9 ambientes = 18 residentes
                        10 => 20, // 10 ambientes = 20 residentes
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
