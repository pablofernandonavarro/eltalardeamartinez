<?php

namespace App\Observers;

use App\Events\ResponsibleChanged;
use App\Models\UnitUser;

class UnitUserObserver
{
    /**
     * Handle the UnitUser "created" event.
     */
    public function created(UnitUser $unitUser): void
    {
        if ($unitUser->is_responsible) {
            ResponsibleChanged::dispatch($unitUser);
        }
    }

    /**
     * Handle the UnitUser "updated" event.
     */
    public function updated(UnitUser $unitUser): void
    {
        if ($unitUser->isDirty('is_responsible') && $unitUser->is_responsible) {
            ResponsibleChanged::dispatch($unitUser);
        }

        if ($unitUser->isDirty('ended_at') && $unitUser->ended_at !== null) {
            ResponsibleChanged::dispatch($unitUser);
        }
    }

    /**
     * Handle the UnitUser "deleted" event.
     */
    public function deleted(UnitUser $unitUser): void
    {
        //
    }

    /**
     * Handle the UnitUser "restored" event.
     */
    public function restored(UnitUser $unitUser): void
    {
        //
    }

    /**
     * Handle the UnitUser "force deleted" event.
     */
    public function forceDeleted(UnitUser $unitUser): void
    {
        //
    }
}
