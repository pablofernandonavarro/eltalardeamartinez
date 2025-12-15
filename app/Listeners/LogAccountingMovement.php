<?php

namespace App\Listeners;

use App\Events\PaymentRegistered;
use Illuminate\Support\Facades\Log;

class LogAccountingMovement
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentRegistered $event): void
    {
        $payment = $event->payment;
        $expenseDetail = $payment->expenseDetail;
        $expense = $expenseDetail->expense;

        Log::channel('accounting')->info('Accounting movement registered', [
            'payment_id' => $payment->id,
            'expense_id' => $expense->id,
            'expense_detail_id' => $expenseDetail->id,
            'unit_id' => $expenseDetail->unit_id,
            'amount' => $payment->amount,
            'payment_date' => $payment->payment_date,
            'payment_method' => $payment->payment_method,
            'reference' => $payment->reference,
        ]);
    }
}
