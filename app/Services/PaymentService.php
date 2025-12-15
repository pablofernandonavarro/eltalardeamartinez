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
        return DB::transaction(function () use (
            $expenseDetail,
            $userId,
            $amount,
            $paymentDate,
            $paymentMethod,
            $reference,
            $notes
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

            $this->updateExpenseDetail($expenseDetail, $amount);

            PaymentRegistered::dispatch($payment);

            return $payment;
        });
    }

    /**
     * Update expense detail with payment amount.
     */
    protected function updateExpenseDetail(ExpenseDetail $expenseDetail, float $amount): void
    {
        $expenseDetail->paid_amount += $amount;
        $expenseDetail->updateStatus();

        if ($expenseDetail->isFullyPaid()) {
            ExpensePaid::dispatch($expenseDetail);
        }
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
