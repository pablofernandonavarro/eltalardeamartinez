<?php

namespace Database\Factories;

use App\Models\Complex;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'complex_id' => Complex::factory(),
            'name' => fake()->word() . ' ' . fake()->buildingNumber(),
            'address' => fake()->streetAddress(),
            'floors' => fake()->numberBetween(1, 20),
            'notes' => null,
        ];
    }
}
