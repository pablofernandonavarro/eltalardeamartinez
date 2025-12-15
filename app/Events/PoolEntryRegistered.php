<?php

namespace App\Events;

use App\Models\PoolEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PoolEntryRegistered
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PoolEntry $poolEntry
    ) {
        //
    }
}
