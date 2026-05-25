<?php

namespace App\Services;

use App\Events\ExpensePaid;
use App\Events\PaymentRegistered;
use App\Models\ExpenseDetail;
use App\Models\Payment;
use App\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Register a payment for an expense detail.
     */
    public function registerPayment(
        ExpenseDetail $expenseDetail,
        int $userId,
        float $amount,
        string $paymentDate,
        ?string $paymentMethod = null,
        ?string $reference = null,
        ?string $notes = null
    ): Payment {
        $wasFullyPaid = false;

        $payment = DB::transaction(function () use (
            $expenseDetail,
            $userId,
            $amount,
            $paymentDate,
            $paymentMethod,
            $reference,
            $notes,
            &$wasFullyPaid
        ) {
            $payment = Payment::create([
                'expense_detail_id' => $expenseDetail->id,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'status' => PaymentStatus::Procesado,
                'notes' => $notes,
            ]);

            $expenseDetail->paid_amount += $amount;
            $expenseDetail->updateStatus();
            $wasFullyPaid = $expenseDetail->isFullyPaid();

            return $payment;
        });

        // Dispatch events after the transaction commits to avoid side effects on rollback.
        PaymentRegistered::dispatch($payment);

        if ($wasFullyPaid) {
            ExpensePaid::dispatch($expenseDetail);
        }

        return $payment;
    }

    /**
     * Update expense detail with payment amount.
     */
    protected function updateExpenseDetail(ExpenseDetail $expenseDetail, float $amount): void
    {
        $expenseDetail->paid_amount += $amount;
        $expenseDetail->updateStatus();
    }

    /**
     * Cancel a payment.
     */
    public function cancelPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->status = PaymentStatus::Anulado;
            $payment->save();

            $expenseDetail = $payment->expenseDetail;
            $expenseDetail->paid_amount -= $payment->amount;
            $expenseDetail->updateStatus();
        });
    }
}
