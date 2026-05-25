<?php

namespace Database\Factories;

use App\Models\ExpenseDetail;
use App\Models\User;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expense_detail_id' => ExpenseDetail::factory(),
            'user_id'           => User::factory()->withoutTwoFactor(),
            'amount'            => fake()->randomFloat(2, 100, 5000),
            'payment_date'      => now()->toDateString(),
            'payment_method'    => fake()->randomElement(['cash', 'transfer', 'card']),
            'reference'         => fake()->optional()->uuid(),
            'status'            => PaymentStatus::Procesado,
            'notes'             => null,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(['status' => PaymentStatus::Pendiente]);
    }

    public function procesado(): static
    {
        return $this->state(['status' => PaymentStatus::Procesado]);
    }

    public function anulado(): static
    {
        return $this->state(['status' => PaymentStatus::Anulado]);
    }
}
