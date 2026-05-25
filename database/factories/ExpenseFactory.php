<?php

namespace Database\Factories;

use App\ExpenseType;
use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'building_id'  => Building::factory(),
            'concept_id'   => null,
            'type'         => ExpenseType::Ordinaria,
            'period'       => now()->format('Y-m'),
            'due_date'     => now()->addDays(30)->toDateString(),
            'total_amount' => fake()->randomFloat(2, 1000, 50000),
            'description'  => fake()->optional()->sentence(),
            'metadata'     => null,
        ];
    }

    public function ordinaria(): static
    {
        return $this->state(['type' => ExpenseType::Ordinaria]);
    }

    public function extraordinaria(): static
    {
        return $this->state(['type' => ExpenseType::Extraordinaria]);
    }

    public function overdue(): static
    {
        return $this->state(['due_date' => now()->subDays(10)->toDateString()]);
    }

    public function dueFuture(): static
    {
        return $this->state(['due_date' => now()->addDays(30)->toDateString()]);
    }
}
