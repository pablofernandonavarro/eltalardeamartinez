<?php

namespace App\Listeners;

use App\Events\PaymentRegistered;

class UpdateExpenseStatus
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
     * Note: The status is already updated in PaymentService,
     * this listener can be used for additional logic if needed.
     */
    public function handle(PaymentRegistered $event): void
    {
        $expenseDetail = $event->payment->expenseDetail;
        $expense = $expenseDetail->expense;

        // Additional logic can be added here, such as:
        // - Updating building-level statistics
        // - Checking if all expenses are paid
        // - Triggering notifications
    }
}
