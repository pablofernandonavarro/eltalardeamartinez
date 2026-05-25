<?php

// Detected: PestPHP v3

use App\Enums\SumReservationStatus;
use App\Models\SumReservation;
use App\Models\Unit;
use App\Models\User;

describe('SumReservation model', function () {

    describe('creation and fillable', function () {
        it('can be created with factory defaults', function () {
            $reservation = SumReservation::factory()->create();

            expect($reservation)->toBeInstanceOf(SumReservation::class)
                ->and($reservation->exists)->toBeTrue()
                ->and($reservation->status)->toBe(SumReservationStatus::Pending);
        });

        it('mass-assigns all fillable fields', function () {
            $unit = Unit::factory()->create();
            $user = User::factory()->withoutTwoFactor()->create();

            $reservation = SumReservation::create([
                'unit_id'        => $unit->id,
                'user_id'        => $user->id,
                'date'           => '2026-07-10',
                'start_time'     => '09:00:00',
                'end_time'       => '11:00:00',
                'total_hours'    => '2.00',
                'price_per_hour' => '5000.00',
                'total_amount'   => '10000.00',
                'status'         => SumReservationStatus::Pending,
                'notes'          => 'Cumpleaños',
                'admin_notes'    => null,
                'approved_by'    => null,
                'approved_at'    => null,
                'rejected_by'    => null,
                'rejected_at'    => null,
                'rejection_reason'    => null,
                'cancelled_by'        => null,
                'cancelled_at'        => null,
                'cancellation_reason' => null,
            ]);

            expect($reservation->unit_id)->toBe($unit->id)
                ->and($reservation->user_id)->toBe($user->id)
                ->and($reservation->notes)->toBe('Cumpleaños')
                ->and((float) $reservation->total_amount)->toBe(10000.0);
        });

        it('casts date as a Carbon date', function () {
            $reservation = SumReservation::factory()->create(['date' => '2026-08-15']);

            expect($reservation->fresh()->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
                ->and($reservation->fresh()->date->format('Y-m-d'))->toBe('2026-08-15');
        });

        it('casts total_hours and price_per_hour as decimal strings', function () {
            $reservation = SumReservation::factory()->create([
                'total_hours'    => 3.5,
                'price_per_hour' => 4500.00,
            ]);

            expect($reservation->fresh()->total_hours)->toBe('3.50')
                ->and($reservation->fresh()->price_per_hour)->toBe('4500.00');
        });

        it('casts status as SumReservationStatus enum', function () {
            $reservation = SumReservation::factory()->approved()->create();

            expect($reservation->fresh()->status)->toBe(SumReservationStatus::Approved);
        });
    });

    describe('soft deletes', function () {
        it('soft deletes a reservation', function () {
            $reservation = SumReservation::factory()->create();
            $id = $reservation->id;

            $reservation->delete();

            expect(SumReservation::find($id))->toBeNull()
                ->and(SumReservation::withTrashed()->find($id))->not->toBeNull();
        });

        it('can restore a soft-deleted reservation', function () {
            $reservation = SumReservation::factory()->create();
            $reservation->delete();

            $reservation->restore();

            expect(SumReservation::find($reservation->id))->not->toBeNull();
        });
    });

    describe('relationships', function () {
        it('belongs to a Unit', function () {
            $unit = Unit::factory()->create();
            $reservation = SumReservation::factory()->create(['unit_id' => $unit->id]);

            expect($reservation->unit)->toBeInstanceOf(Unit::class)
                ->and($reservation->unit->id)->toBe($unit->id);
        });

        it('belongs to a User', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $reservation = SumReservation::factory()->create(['user_id' => $user->id]);

            expect($reservation->user)->toBeInstanceOf(User::class)
                ->and($reservation->user->id)->toBe($user->id);
        });

        it('belongs to an approvedBy user', function () {
            $admin = User::factory()->withoutTwoFactor()->create();
            $reservation = SumReservation::factory()->create([
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'status'      => SumReservationStatus::Approved,
            ]);

            expect($reservation->approvedBy)->toBeInstanceOf(User::class)
                ->and($reservation->approvedBy->id)->toBe($admin->id);
        });

        it('belongs to a rejectedBy user', function () {
            $admin = User::factory()->withoutTwoFactor()->create();
            $reservation = SumReservation::factory()->create([
                'rejected_by'      => $admin->id,
                'rejected_at'      => now(),
                'rejection_reason' => 'No disponible.',
                'status'           => SumReservationStatus::Rejected,
            ]);

            expect($reservation->rejectedBy)->toBeInstanceOf(User::class)
                ->and($reservation->rejectedBy->id)->toBe($admin->id);
        });

        it('belongs to a cancelledBy user', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $reservation = SumReservation::factory()->create([
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'status'       => SumReservationStatus::Cancelled,
            ]);

            expect($reservation->cancelledBy)->toBeInstanceOf(User::class)
                ->and($reservation->cancelledBy->id)->toBe($user->id);
        });

        it('returns null for optional FK relationships when not set', function () {
            $reservation = SumReservation::factory()->pending()->create();

            expect($reservation->approvedBy)->toBeNull()
                ->and($reservation->rejectedBy)->toBeNull()
                ->and($reservation->cancelledBy)->toBeNull();
        });
    });

    describe('scopes', function () {
        it('scopePending returns only pending reservations', function () {
            SumReservation::factory()->pending()->create();
            SumReservation::factory()->approved()->create();
            SumReservation::factory()->rejected()->create();

            $results = SumReservation::pending()->get();

            expect($results)->toHaveCount(1)
                ->and($results->first()->status)->toBe(SumReservationStatus::Pending);
        });

        it('scopeApproved returns only approved reservations', function () {
            SumReservation::factory()->pending()->create();
            SumReservation::factory()->approved()->create();
            SumReservation::factory()->approved()->create();

            $results = SumReservation::approved()->get();

            expect($results)->toHaveCount(2);
            $results->each(fn ($r) => expect($r->status)->toBe(SumReservationStatus::Approved));
        });

        it('scopeActive returns pending and approved reservations only', function () {
            SumReservation::factory()->pending()->create();
            SumReservation::factory()->approved()->create();
            SumReservation::factory()->rejected()->create();
            SumReservation::factory()->cancelled()->create();
            SumReservation::factory()->completed()->create();

            $results = SumReservation::active()->get();

            expect($results)->toHaveCount(2);
            $results->each(fn ($r) => expect($r->status->isActive())->toBeTrue());
        });

        it('scopeForDate returns only reservations on the given date', function () {
            SumReservation::factory()->forDate('2026-07-10')->create();
            SumReservation::factory()->forDate('2026-07-10')->create();
            SumReservation::factory()->forDate('2026-07-11')->create();

            $results = SumReservation::forDate('2026-07-10')->get();

            expect($results)->toHaveCount(2);
        });

        it('scopeForUnit returns only reservations for the given unit', function () {
            $unit = Unit::factory()->create();
            SumReservation::factory()->create(['unit_id' => $unit->id]);
            SumReservation::factory()->create(['unit_id' => $unit->id]);
            SumReservation::factory()->create(); // different unit

            $results = SumReservation::forUnit($unit->id)->get();

            expect($results)->toHaveCount(2);
            $results->each(fn ($r) => expect($r->unit_id)->toBe($unit->id));
        });

        it('scopeUpcoming returns reservations from today onwards ordered by date then start_time', function () {
            $past = SumReservation::factory()->create([
                'date'       => now()->subDays(2)->toDateString(),
                'start_time' => '10:00:00',
                'end_time'   => '12:00:00',
            ]);
            $today = SumReservation::factory()->create([
                'date'       => now()->toDateString(),
                'start_time' => '14:00:00',
                'end_time'   => '16:00:00',
            ]);
            $future = SumReservation::factory()->create([
                'date'       => now()->addDays(5)->toDateString(),
                'start_time' => '09:00:00',
                'end_time'   => '11:00:00',
            ]);

            $results = SumReservation::upcoming()->get();

            expect($results)->toHaveCount(2)
                ->and($results->contains($past))->toBeFalse()
                ->and($results->first()->id)->toBe($today->id)
                ->and($results->last()->id)->toBe($future->id);
        });

        it('scopeActive excludes soft-deleted records', function () {
            $reservation = SumReservation::factory()->pending()->create();
            $reservation->delete();

            expect(SumReservation::active()->count())->toBe(0);
        });
    });

    describe('hasOverlap', function () {
        beforeEach(function () {
            // Existing active reservation on a fixed date: 10:00 - 12:00
            SumReservation::factory()->forDate('2026-07-15')->withTimeSlot('10:00:00', '12:00:00')->pending()->create();
        });

        it('returns false when there is no overlap (slot completely before)', function () {
            // 08:00 - 09:30 does not overlap with 10:00 - 12:00
            $result = SumReservation::hasOverlap('2026-07-15', '08:00:00', '09:30:00');

            expect($result)->toBeFalse();
        });

        it('returns false when there is no overlap (slot completely after)', function () {
            // 12:00 - 14:00 starts exactly when existing ends — no overlap
            $result = SumReservation::hasOverlap('2026-07-15', '12:00:00', '14:00:00');

            expect($result)->toBeFalse();
        });

        it('returns true for exact same time slot', function () {
            $result = SumReservation::hasOverlap('2026-07-15', '10:00:00', '12:00:00');

            expect($result)->toBeTrue();
        });

        it('returns true when new slot starts during existing (partial overlap at start)', function () {
            // New: 11:00 - 13:00 — overlaps with existing 10:00 - 12:00
            $result = SumReservation::hasOverlap('2026-07-15', '11:00:00', '13:00:00');

            expect($result)->toBeTrue();
        });

        it('returns true when new slot ends during existing (partial overlap at end)', function () {
            // New: 09:00 - 11:00 — overlaps with existing 10:00 - 12:00
            $result = SumReservation::hasOverlap('2026-07-15', '09:00:00', '11:00:00');

            expect($result)->toBeTrue();
        });

        it('returns true when new slot completely contains existing slot', function () {
            // New: 09:00 - 13:00 — completely wraps existing 10:00 - 12:00
            $result = SumReservation::hasOverlap('2026-07-15', '09:00:00', '13:00:00');

            expect($result)->toBeTrue();
        });

        it('returns false when checking a different date', function () {
            $result = SumReservation::hasOverlap('2026-07-16', '10:00:00', '12:00:00');

            expect($result)->toBeFalse();
        });

        it('excludes a specific reservation id from the overlap check', function () {
            $existing = SumReservation::query()
                ->whereDate('date', '2026-07-15')
                ->first();

            // Editing the same reservation — should not detect overlap with itself
            $result = SumReservation::hasOverlap('2026-07-15', '10:00:00', '12:00:00', $existing->id);

            expect($result)->toBeFalse();
        });

        it('does not count cancelled reservations as overlapping', function () {
            SumReservation::factory()
                ->forDate('2026-07-15')
                ->withTimeSlot('10:00:00', '12:00:00')
                ->cancelled()
                ->create();

            // Only one active reservation exists (from beforeEach)
            $cancelledCount = SumReservation::where('status', \App\Enums\SumReservationStatus::Cancelled)
                ->whereDate('date', '2026-07-15')->count();
            expect($cancelledCount)->toBe(1);

            // A slot that doesn't overlap the active one is still fine
            $result = SumReservation::hasOverlap('2026-07-15', '13:00:00', '15:00:00');
            expect($result)->toBeFalse();
        });

        it('does not count rejected reservations as overlapping', function () {
            SumReservation::factory()
                ->forDate('2026-07-20')
                ->withTimeSlot('10:00:00', '12:00:00')
                ->rejected()
                ->create();

            $result = SumReservation::hasOverlap('2026-07-20', '10:00:00', '12:00:00');

            expect($result)->toBeFalse();
        });
    });

    describe('accessors', function () {
        it('time_range returns formatted HH:MM - HH:MM string', function () {
            $reservation = SumReservation::factory()->withTimeSlot('09:00:00', '11:30:00')->create();

            expect($reservation->time_range)->toBe('09:00 - 11:30');
        });

        it('status_label returns the enum label', function () {
            $pending   = SumReservation::factory()->pending()->create();
            $approved  = SumReservation::factory()->approved()->create();
            $rejected  = SumReservation::factory()->rejected()->create();
            $cancelled = SumReservation::factory()->cancelled()->create();
            $completed = SumReservation::factory()->completed()->create();

            expect($pending->status_label)->toBe('Pendiente')
                ->and($approved->status_label)->toBe('Aprobada')
                ->and($rejected->status_label)->toBe('Rechazada')
                ->and($cancelled->status_label)->toBe('Cancelada')
                ->and($completed->status_label)->toBe('Completada');
        });

        it('status_color returns the enum color', function () {
            $pending  = SumReservation::factory()->pending()->create();
            $approved = SumReservation::factory()->approved()->create();

            expect($pending->status_color)->toBe('amber')
                ->and($approved->status_color)->toBe('green');
        });
    });
});
