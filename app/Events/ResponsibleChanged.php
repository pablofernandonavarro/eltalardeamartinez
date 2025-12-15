<?php

namespace App\Events;

use App\Models\UnitUser;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResponsibleChanged
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public UnitUser $unitUser
    ) {
        //
    }
}
