<?php

namespace Database\Factories;

use App\Enums\SumReservationStatus;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SumReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'unit_id'        => Unit::factory(),
            'user_id'        => User::factory()->withoutTwoFactor(),
            'date'           => now()->addDays(fake()->numberBetween(1, 30))->toDateString(),
            'start_time'     => '10:00:00',
            'end_time'       => '12:00:00',
            'total_hours'    => '2.00',
            'price_per_hour' => '5000.00',
            'total_amount'   => '10000.00',
            'status'         => SumReservationStatus::Pending,
            'notes'          => null,
            'admin_notes'    => null,
            'approved_by'    => null,
            'approved_at'    => null,
            'rejected_by'    => null,
            'rejected_at'    => null,
            'rejection_reason'    => null,
            'cancelled_by'        => null,
            'cancelled_at'        => null,
            'cancellation_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => SumReservationStatus::Pending]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => SumReservationStatus::Approved,
            'approved_by' => User::factory()->withoutTwoFactor(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status'           => SumReservationStatus::Rejected,
            'rejected_by'      => User::factory()->withoutTwoFactor(),
            'rejected_at'      => now(),
            'rejection_reason' => 'No disponible.',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'              => SumReservationStatus::Cancelled,
            'cancelled_by'        => User::factory()->withoutTwoFactor(),
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Cancelado por el usuario.',
        ]);
    }

    public function completed(): static
    {
        return $this->state(['status' => SumReservationStatus::Completed]);
    }

    public function forDate(string $date): static
    {
        return $this->state(['date' => $date]);
    }

    public function withTimeSlot(string $startTime, string $endTime): static
    {
        return $this->state([
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);
    }
}
