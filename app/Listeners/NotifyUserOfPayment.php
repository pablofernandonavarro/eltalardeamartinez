<?php

namespace App\Listeners;

use App\Events\PaymentRegistered;
use Illuminate\Support\Facades\Log;

class NotifyUserOfPayment
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
        $user = $payment->user;
        $expenseDetail = $payment->expenseDetail;

        // Log the payment notification
        Log::info('Payment registered', [
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'expense_detail_id' => $expenseDetail->id,
        ]);

        // Here you can add email notifications, SMS, etc.
        // Example: Mail::to($user)->send(new PaymentConfirmation($payment));
    }
}
