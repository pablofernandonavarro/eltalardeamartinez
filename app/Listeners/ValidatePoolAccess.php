<?php

namespace App\Listeners;

use App\Events\PoolEntryRegistered;
use Illuminate\Support\Facades\Log;

class ValidatePoolAccess
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
     * Note: Validation is already done in PoolAccessService,
     * this listener can be used for post-validation logic.
     */
    public function handle(PoolEntryRegistered $event): void
    {
        $entry = $event->poolEntry;

        Log::info('Pool entry registered', [
            'pool_id' => $entry->pool_id,
            'unit_id' => $entry->unit_id,
            'user_id' => $entry->user_id,
            'guests_count' => $entry->guests_count,
            'entered_at' => $entry->entered_at,
        ]);

        // Additional post-validation logic can be added here
    }
}
