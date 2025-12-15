<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Complex;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ComplexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $complex = Complex::create([
            'name' => 'El Talar de Martínez',
            'address' => 'Av. Libertador 1234',
            'city' => 'Martínez',
            'province' => 'Buenos Aires',
            'postal_code' => '1640',
            'phone' => '+54 11 1234-5678',
            'email' => 'admin@eltalardemartinez.com',
        ]);

        $building1 = Building::create([
            'complex_id' => $complex->id,
            'name' => 'Torre A',
            'address' => 'Av. Libertador 1234',
            'floors' => 10,
        ]);

        $building2 = Building::create([
            'complex_id' => $complex->id,
            'name' => 'Torre B',
            'address' => 'Av. Libertador 1234',
            'floors' => 8,
        ]);

        // Create units for Building 1 (Torre A)
        for ($floor = 1; $floor <= 10; $floor++) {
            for ($unit = 1; $unit <= 4; $unit++) {
                Unit::create([
                    'building_id' => $building1->id,
                    'number' => "{$floor}{$unit}",
                    'floor' => (string) $floor,
                    'coefficient' => 1.0000 + ($unit * 0.05),
                    'rooms' => 2 + ($unit % 2),
                    'area' => 60.00 + ($unit * 10),
                ]);
            }
        }

        // Create units for Building 2 (Torre B)
        for ($floor = 1; $floor <= 8; $floor++) {
            for ($unit = 1; $unit <= 3; $unit++) {
                Unit::create([
                    'building_id' => $building2->id,
                    'number' => "{$floor}{$unit}",
                    'floor' => (string) $floor,
                    'coefficient' => 1.0000 + ($unit * 0.05),
                    'rooms' => 2 + ($unit % 2),
                    'area' => 55.00 + ($unit * 10),
                ]);
            }
        }
    }
}
