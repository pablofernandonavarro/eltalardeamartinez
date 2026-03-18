<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComplexFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'notes' => null,
        ];
    }
}
