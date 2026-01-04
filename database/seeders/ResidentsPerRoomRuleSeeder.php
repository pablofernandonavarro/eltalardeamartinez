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
                    'residents_per_room' => 2, // 2 residentes por ambiente
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
