<?php

namespace Database\Factories;

use App\Enums\SumPaymentStatus;
use App\Models\SumReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SumPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id'        => SumReservation::factory(),
            'amount'                => '10000.00',
            'status'                => SumPaymentStatus::Pending,
            'payment_method'        => null,
            'transaction_reference' => null,
            'notes'                 => null,
            'paid_at'               => null,
            'paid_by'               => null,
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status'         => SumPaymentStatus::Pending,
            'payment_method' => null,
            'paid_at'        => null,
            'paid_by'        => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'                => SumPaymentStatus::Paid,
            'payment_method'        => fake()->randomElement(['cash', 'transfer', 'card', 'online']),
            'transaction_reference' => fake()->optional()->uuid(),
            'paid_at'               => now(),
            'paid_by'               => User::factory()->withoutTwoFactor(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => SumPaymentStatus::Cancelled,
        ]);
    }
}
