<?php

namespace App\Listeners;

use App\Events\PoolEntryRegistered;
use Illuminate\Support\Facades\Log;

class NotifyAdminPoolViolation
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
     * This listener can check for violations and notify admins.
     */
    public function handle(PoolEntryRegistered $event): void
    {
        $entry = $event->poolEntry;
        $pool = $entry->pool;
        $rule = $pool->currentRule;

        if (! $rule) {
            return;
        }

        $violations = [];

        if ($rule->max_guests_per_unit > 0 && $entry->guests_count > $rule->max_guests_per_unit) {
            $violations[] = 'Exceso de invitados';
        }

        if ($rule->max_entries_per_day > 0) {
            $todayEntries = $entry->unit->poolEntries()
                ->where('pool_id', $pool->id)
                ->forDate($entry->entered_at->toDateString())
                ->count();

            if ($todayEntries > $rule->max_entries_per_day) {
                $violations[] = 'Exceso de ingresos diarios';
            }
        }

        if (! empty($violations)) {
            Log::warning('Pool access violation detected', [
                'entry_id' => $entry->id,
                'pool_id' => $pool->id,
                'unit_id' => $entry->unit_id,
                'user_id' => $entry->user_id,
                'violations' => $violations,
            ]);

            // Here you can add admin notification logic
            // Example: Mail::to($admin)->send(new PoolViolationAlert($entry, $violations));
        }
    }
}
