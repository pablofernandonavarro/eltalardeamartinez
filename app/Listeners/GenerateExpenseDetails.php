<?php

namespace App\Listeners;

use App\Events\ExpenseCreated;
use App\Services\ExpenseService;

class GenerateExpenseDetails
{
    /**
     * Create the event listener.
     */
    public function __construct(
        public ExpenseService $expenseService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ExpenseCreated $event): void
    {
        $this->expenseService->generateExpenseDetails($event->expense);
    }
}
