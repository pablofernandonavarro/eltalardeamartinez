<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'number' => fake()->numerify('###'),
            'uf_code' => fake()->optional()->bothify('UF-###'),
            'floor' => fake()->numberBetween(1, 20),
            'coefficient' => fake()->randomFloat(4, 0.01, 10),
            'rooms' => fake()->numberBetween(1, 5),
            'terrazas' => fake()->numberBetween(0, 2),
            'area' => fake()->randomFloat(2, 30, 300),
            'max_residents' => fake()->numberBetween(1, 10),
            'has_pets' => false,
            'notes' => null,
            'owner' => fake()->name(),
        ];
    }
}
