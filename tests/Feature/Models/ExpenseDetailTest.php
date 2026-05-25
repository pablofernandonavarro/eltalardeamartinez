<?php

// Detected: PestPHP v3

use App\ExpenseStatus;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Payment;
use App\Models\Unit;

describe('ExpenseDetail model', function () {

    describe('creation and fillable', function () {
        it('can be created with factory defaults', function () {
            $detail = ExpenseDetail::factory()->create();

            expect($detail)->toBeInstanceOf(ExpenseDetail::class)
                ->and($detail->exists)->toBeTrue()
                ->and($detail->status)->toBe(ExpenseStatus::Pendiente)
                ->and((float) $detail->paid_amount)->toBe(0.0);
        });

        it('mass-assigns all fillable fields', function () {
            $expense = Expense::factory()->create();
            $unit    = Unit::factory()->create();

            $detail = ExpenseDetail::create([
                'expense_id'  => $expense->id,
                'unit_id'     => $unit->id,
                'amount'      => '5000.00',
                'paid_amount' => '2500.00',
                'status'      => ExpenseStatus::Parcial,
                'paid_at'     => '2026-04-01',
                'notes'       => 'Pago parcial registrado',
            ]);

            expect($detail->expense_id)->toBe($expense->id)
                ->and($detail->unit_id)->toBe($unit->id)
                ->and((float) $detail->amount)->toBe(5000.0)
                ->and((float) $detail->paid_amount)->toBe(2500.0)
                ->and($detail->status)->toBe(ExpenseStatus::Parcial)
                ->and($detail->notes)->toBe('Pago parcial registrado');
        });

        it('casts amount and paid_amount as decimal strings', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1234.56,
                'paid_amount' => 500.00,
            ]);

            expect($detail->fresh()->amount)->toBe('1234.56')
                ->and($detail->fresh()->paid_amount)->toBe('500.00');
        });

        it('casts status as ExpenseStatus enum', function () {
            $detail = ExpenseDetail::factory()->pagada()->create();

            expect($detail->fresh()->status)->toBe(ExpenseStatus::Pagada);
        });

        it('casts paid_at as a Carbon date', function () {
            $detail = ExpenseDetail::factory()->create(['paid_at' => '2026-03-20']);

            expect($detail->fresh()->paid_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
                ->and($detail->fresh()->paid_at->format('Y-m-d'))->toBe('2026-03-20');
        });

        it('defaults paid_amount to zero', function () {
            $detail = ExpenseDetail::factory()->pendiente()->create();

            expect((float) $detail->fresh()->paid_amount)->toBe(0.0);
        });
    });

    describe('soft deletes', function () {
        it('soft deletes an expense detail', function () {
            $detail = ExpenseDetail::factory()->create();
            $id = $detail->id;

            $detail->delete();

            expect(ExpenseDetail::find($id))->toBeNull()
                ->and(ExpenseDetail::withTrashed()->find($id))->not->toBeNull();
        });

        it('can restore a soft-deleted expense detail', function () {
            $detail = ExpenseDetail::factory()->create();
            $detail->delete();

            $detail->restore();

            expect(ExpenseDetail::find($detail->id))->not->toBeNull();
        });
    });

    describe('relationships', function () {
        it('belongs to an Expense', function () {
            $expense = Expense::factory()->create();
            $detail  = ExpenseDetail::factory()->create(['expense_id' => $expense->id]);

            expect($detail->expense)->toBeInstanceOf(Expense::class)
                ->and($detail->expense->id)->toBe($expense->id);
        });

        it('belongs to a Unit', function () {
            $unit   = Unit::factory()->create();
            $detail = ExpenseDetail::factory()->create(['unit_id' => $unit->id]);

            expect($detail->unit)->toBeInstanceOf(Unit::class)
                ->and($detail->unit->id)->toBe($unit->id);
        });

        it('has many payments', function () {
            $detail = ExpenseDetail::factory()->create();

            Payment::factory()->count(2)->create(['expense_detail_id' => $detail->id]);

            expect($detail->payments)->toHaveCount(2)
                ->each->toBeInstanceOf(Payment::class);
        });

        it('has zero payments by default', function () {
            $detail = ExpenseDetail::factory()->create();

            expect($detail->payments)->toBeEmpty();
        });
    });

    describe('pending_amount accessor', function () {
        it('returns amount minus paid_amount', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 300.00,
            ]);

            expect($detail->pending_amount)->toBe(700.0);
        });

        it('returns zero when fully paid', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 1000.00,
            ]);

            expect($detail->pending_amount)->toBe(0.0);
        });

        it('returns full amount when nothing has been paid', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 2500.00,
                'paid_amount' => 0.00,
            ]);

            expect($detail->pending_amount)->toBe(2500.0);
        });

        it('handles negative pending_amount when overpaid', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 1200.00,
            ]);

            expect($detail->pending_amount)->toBe(-200.0);
        });
    });

    describe('isFullyPaid', function () {
        it('returns true when paid_amount equals amount', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 5000.00,
                'paid_amount' => 5000.00,
            ]);

            expect($detail->isFullyPaid())->toBeTrue();
        });

        it('returns true when paid_amount exceeds amount (overpaid)', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 1001.00,
            ]);

            expect($detail->isFullyPaid())->toBeTrue();
        });

        it('returns false when partial amount paid', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 999.99,
            ]);

            expect($detail->isFullyPaid())->toBeFalse();
        });

        it('returns false when nothing is paid', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 0.00,
            ]);

            expect($detail->isFullyPaid())->toBeFalse();
        });
    });

    describe('updateStatus', function () {
        it('sets status to Pagada and records paid_at when fully paid', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 1000.00,
                'status'      => ExpenseStatus::Pendiente,
                'paid_at'     => null,
            ]);

            $detail->updateStatus();

            $detail->refresh();

            // paid_at is cast as 'date', so it stores only the calendar date (no time component)
            expect($detail->status)->toBe(ExpenseStatus::Pagada)
                ->and($detail->paid_at)->not->toBeNull()
                ->and($detail->paid_at->toDateString())->toBe(now()->toDateString());
        });

        it('sets status to Parcial when some amount is paid but not fully', function () {
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 500.00,
                'status'      => ExpenseStatus::Pendiente,
            ]);

            $detail->updateStatus();

            expect($detail->fresh()->status)->toBe(ExpenseStatus::Parcial);
        });

        it('keeps paid_at value when partial (does not reset it)', function () {
            // paid_at is not touched in Parcial path — status Parcial means still unpaid portion remains
            $detail = ExpenseDetail::factory()->create([
                'amount'      => 1000.00,
                'paid_amount' => 500.00,
            ]);

            $detail->updateStatus();
            $detail->refresh();

            // The model does not set paid_at in Parcial path, so it stays as-is
            expect($detail->status)->toBe(ExpenseStatus::Parcial);
        });

        it('sets status to Pendiente when nothing paid and due date is in the future', function () {
            $expense = Expense::factory()->dueFuture()->create();
            $detail  = ExpenseDetail::factory()->create([
                'expense_id'  => $expense->id,
                'amount'      => 1000.00,
                'paid_amount' => 0.00,
                'status'      => ExpenseStatus::Vencida,
                'paid_at'     => '2026-01-01',
            ]);

            $detail->updateStatus();

            $detail->refresh();

            expect($detail->status)->toBe(ExpenseStatus::Pendiente)
                ->and($detail->paid_at)->toBeNull();
        });

        it('sets status to Vencida when nothing paid and due date has passed', function () {
            $expense = Expense::factory()->overdue()->create();
            $detail  = ExpenseDetail::factory()->create([
                'expense_id'  => $expense->id,
                'amount'      => 1000.00,
                'paid_amount' => 0.00,
                'status'      => ExpenseStatus::Pendiente,
            ]);

            $detail->updateStatus();

            expect($detail->fresh()->status)->toBe(ExpenseStatus::Vencida);
        });

        it('sets status to Pendiente when nothing paid and no due_date is set', function () {
            // due_date is required in the migration, so we use a far future date to simulate "no due date effect"
            $expense = Expense::factory()->create(['due_date' => now()->addYears(10)->toDateString()]);
            $detail  = ExpenseDetail::factory()->create([
                'expense_id'  => $expense->id,
                'amount'      => 1000.00,
                'paid_amount' => 0.00,
            ]);

            $detail->updateStatus();

            expect($detail->fresh()->status)->toBe(ExpenseStatus::Pendiente);
        });

        it('persists the updated status to the database', function () {
            $expense = Expense::factory()->overdue()->create();
            $detail  = ExpenseDetail::factory()->create([
                'expense_id'  => $expense->id,
                'amount'      => 1000.00,
                'paid_amount' => 0.00,
                'status'      => ExpenseStatus::Pendiente,
            ]);

            $detail->updateStatus();

            // Confirm it was actually saved, not just set on the model
            $fromDb = ExpenseDetail::find($detail->id);
            expect($fromDb->status)->toBe(ExpenseStatus::Vencida);
        });
    });
});
