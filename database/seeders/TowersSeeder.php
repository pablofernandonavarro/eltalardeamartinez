<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Complex;
use Illuminate\Database\Seeder;

class TowersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $complex = Complex::query()->updateOrCreate(
            ['name' => 'El Talar de Martínez'],
            [
                'address' => 'Monseñor Larumbe 3151',
                'city' => 'Martínez',
                'province' => 'Buenos Aires',
                'postal_code' => 'B1640GZK',
                'phone' => '+54 11 1234-5678',
                'email' => 'admin@eltalardemartinez.com',
            ]
        );

        for ($i = 1; $i <= 11; $i++) {
            Building::query()->updateOrCreate(
                ['complex_id' => $complex->id, 'name' => "Torre {$i}"],
                [
                    'address' => 'Monseñor Larumbe 3151, B1640GZK Martínez, Provincia de Buenos Aires',
                    'floors' => 4,
                ]
            );
        }
    }
}
