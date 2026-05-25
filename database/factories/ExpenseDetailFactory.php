<?php

namespace Database\Factories;

use App\ExpenseStatus;
use App\Models\Expense;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseDetailFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 500, 10000);

        return [
            'expense_id'  => Expense::factory(),
            'unit_id'     => Unit::factory(),
            'amount'      => $amount,
            'paid_amount' => '0.00',
            'status'      => ExpenseStatus::Pendiente,
            'paid_at'     => null,
            'notes'       => null,
        ];
    }

    public function pendiente(): static
    {
        return $this->state([
            'paid_amount' => '0.00',
            'status'      => ExpenseStatus::Pendiente,
            'paid_at'     => null,
        ]);
    }

    public function parcial(): static
    {
        return $this->state(fn (array $attrs) => [
            'paid_amount' => round($attrs['amount'] / 2, 2),
            'status'      => ExpenseStatus::Parcial,
            'paid_at'     => null,
        ]);
    }

    public function pagada(): static
    {
        return $this->state(fn (array $attrs) => [
            'paid_amount' => $attrs['amount'],
            'status'      => ExpenseStatus::Pagada,
            'paid_at'     => now()->toDateString(),
        ]);
    }

    public function vencida(): static
    {
        return $this->state([
            'paid_amount' => '0.00',
            'status'      => ExpenseStatus::Vencida,
            'paid_at'     => null,
        ]);
    }
}
