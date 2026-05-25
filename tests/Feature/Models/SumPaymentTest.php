<?php

// Detected: PestPHP v3

use App\Enums\SumPaymentStatus;
use App\Models\SumPayment;
use App\Models\SumReservation;
use App\Models\User;

describe('SumPayment model', function () {

    describe('creation and fillable', function () {
        it('can be created with factory defaults', function () {
            $payment = SumPayment::factory()->create();

            expect($payment)->toBeInstanceOf(SumPayment::class)
                ->and($payment->exists)->toBeTrue()
                ->and($payment->status)->toBe(SumPaymentStatus::Pending);
        });

        it('mass-assigns all fillable fields', function () {
            $reservation = SumReservation::factory()->create();
            $user = User::factory()->withoutTwoFactor()->create();

            $payment = SumPayment::create([
                'reservation_id'        => $reservation->id,
                'amount'                => '1500.50',
                'status'                => SumPaymentStatus::Pending,
                'payment_method'        => 'cash',
                'transaction_reference' => 'TXN-001',
                'notes'                 => 'Pago en efectivo',
                'paid_at'               => null,
                'paid_by'               => null,
            ]);

            expect($payment->reservation_id)->toBe($reservation->id)
                ->and((float) $payment->amount)->toBe(1500.50)
                ->and($payment->payment_method)->toBe('cash')
                ->and($payment->transaction_reference)->toBe('TXN-001')
                ->and($payment->notes)->toBe('Pago en efectivo');
        });

        it('casts amount as decimal string', function () {
            $payment = SumPayment::factory()->create(['amount' => 9999.99]);

            expect($payment->fresh()->amount)->toBe('9999.99');
        });

        it('casts status as SumPaymentStatus enum', function () {
            $payment = SumPayment::factory()->create(['status' => SumPaymentStatus::Paid]);

            expect($payment->fresh()->status)->toBe(SumPaymentStatus::Paid);
        });

        it('casts paid_at as Carbon datetime', function () {
            $payment = SumPayment::factory()->create(['paid_at' => '2026-01-15 10:30:00']);

            expect($payment->fresh()->paid_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        });
    });

    describe('soft deletes', function () {
        it('soft deletes a payment record', function () {
            $payment = SumPayment::factory()->create();
            $id = $payment->id;

            $payment->delete();

            expect(SumPayment::find($id))->toBeNull()
                ->and(SumPayment::withTrashed()->find($id))->not->toBeNull();
        });

        it('can restore a soft-deleted payment', function () {
            $payment = SumPayment::factory()->create();
            $payment->delete();

            $payment->restore();

            expect(SumPayment::find($payment->id))->not->toBeNull();
        });
    });

    describe('relationships', function () {
        it('belongs to a SumReservation', function () {
            $reservation = SumReservation::factory()->create();
            $payment = SumPayment::factory()->create(['reservation_id' => $reservation->id]);

            expect($payment->reservation)->toBeInstanceOf(SumReservation::class)
                ->and($payment->reservation->id)->toBe($reservation->id);
        });

        it('belongs to the user who paid via paidByUser', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $payment = SumPayment::factory()->create(['paid_by' => $user->id]);

            expect($payment->paidByUser)->toBeInstanceOf(User::class)
                ->and($payment->paidByUser->id)->toBe($user->id);
        });

        it('returns null paidByUser when paid_by is null', function () {
            $payment = SumPayment::factory()->create(['paid_by' => null]);

            expect($payment->paidByUser)->toBeNull();
        });
    });

    describe('scopes', function () {
        it('scopePending returns only pending payments', function () {
            SumPayment::factory()->create(['status' => SumPaymentStatus::Pending]);
            SumPayment::factory()->create(['status' => SumPaymentStatus::Paid]);
            SumPayment::factory()->create(['status' => SumPaymentStatus::Cancelled]);

            $results = SumPayment::pending()->get();

            expect($results)->toHaveCount(1)
                ->and($results->first()->status)->toBe(SumPaymentStatus::Pending);
        });

        it('scopePaid returns only paid payments', function () {
            SumPayment::factory()->create(['status' => SumPaymentStatus::Pending]);
            SumPayment::factory()->paid()->create();
            SumPayment::factory()->paid()->create();

            $results = SumPayment::paid()->get();

            expect($results)->toHaveCount(2);
            $results->each(fn ($p) => expect($p->status)->toBe(SumPaymentStatus::Paid));
        });

        it('scopePending excludes soft-deleted records', function () {
            $payment = SumPayment::factory()->create(['status' => SumPaymentStatus::Pending]);
            $payment->delete();

            expect(SumPayment::pending()->count())->toBe(0);
        });
    });

    describe('markAsPaid', function () {
        it('marks payment as paid with all fields', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $payment = SumPayment::factory()->create();

            $this->actingAs($user);

            $payment->markAsPaid('cash', 'TXN-123', 'Pago en recepción');

            $payment->refresh();

            expect($payment->status)->toBe(SumPaymentStatus::Paid)
                ->and($payment->payment_method)->toBe('cash')
                ->and($payment->transaction_reference)->toBe('TXN-123')
                ->and($payment->notes)->toBe('Pago en recepción')
                ->and($payment->paid_at)->not->toBeNull()
                ->and($payment->paid_by)->toBe($user->id);
        });

        it('sets paid_by to the authenticated user id', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $payment = SumPayment::factory()->create();

            $this->actingAs($user);
            $payment->markAsPaid('transfer');

            expect($payment->fresh()->paid_by)->toBe($user->id);
        });

        it('works without optional transactionRef and notes', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $payment = SumPayment::factory()->create();

            $this->actingAs($user);
            $payment->markAsPaid('card');

            $payment->refresh();

            expect($payment->status)->toBe(SumPaymentStatus::Paid)
                ->and($payment->transaction_reference)->toBeNull()
                ->and($payment->notes)->toBeNull();
        });

        it('records paid_at timestamp as current time', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $payment = SumPayment::factory()->create();

            $now = now();
            $this->actingAs($user);
            $payment->markAsPaid('online');

            expect($payment->fresh()->paid_at->timestamp)
                ->toBeGreaterThanOrEqual($now->timestamp);
        });
    });

    describe('markAsCancelled', function () {
        it('marks a pending payment as cancelled', function () {
            $payment = SumPayment::factory()->create(['status' => SumPaymentStatus::Pending]);

            $payment->markAsCancelled();

            expect($payment->fresh()->status)->toBe(SumPaymentStatus::Cancelled);
        });

        it('can also cancel a paid payment', function () {
            $user = User::factory()->withoutTwoFactor()->create();
            $payment = SumPayment::factory()->paid()->create(['paid_by' => $user->id]);

            $payment->markAsCancelled();

            expect($payment->fresh()->status)->toBe(SumPaymentStatus::Cancelled);
        });
    });

    describe('accessors', function () {
        it('status_label returns the enum label', function () {
            $pending = SumPayment::factory()->create(['status' => SumPaymentStatus::Pending]);
            $paid = SumPayment::factory()->paid()->create();
            $cancelled = SumPayment::factory()->cancelled()->create();

            expect($pending->status_label)->toBe('Pendiente')
                ->and($paid->status_label)->toBe('Pagado')
                ->and($cancelled->status_label)->toBe('Cancelado');
        });

        it('payment_method_label returns dash when method is null', function () {
            $payment = SumPayment::factory()->create(['payment_method' => null]);

            expect($payment->payment_method_label)->toBe('-');
        });

        it('payment_method_label translates known methods', function (string $method, string $expected) {
            $payment = SumPayment::factory()->create(['payment_method' => $method]);

            expect($payment->payment_method_label)->toBe($expected);
        })->with([
            ['cash', 'Efectivo'],
            ['transfer', 'Transferencia'],
            ['card', 'Tarjeta'],
            ['online', 'Pago Online'],
        ]);

        it('payment_method_label returns raw value for unknown methods', function () {
            // Cannot insert 'crypto' due to the DB CHECK constraint on payment_method.
            // Test the accessor directly by setting the attribute without persisting.
            $payment = new SumPayment();
            $payment->payment_method = 'crypto';

            expect($payment->payment_method_label)->toBe('crypto');
        });
    });
});
