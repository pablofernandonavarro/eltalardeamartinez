<?php

namespace App\Events;

use App\Models\ExpenseDetail;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpensePaid
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ExpenseDetail $expenseDetail
    ) {
        //
    }
}
