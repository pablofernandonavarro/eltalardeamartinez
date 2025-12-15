<?php

namespace App\Events;

use App\Models\PoolRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PoolRuleUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PoolRule $poolRule
    ) {
        //
    }
}
