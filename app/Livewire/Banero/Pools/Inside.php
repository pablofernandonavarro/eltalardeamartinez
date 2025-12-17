<?php

namespace App\Livewire\Banero\Pools;

use App\Models\Pool;
use App\Models\PoolEntry;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Inside extends Component
{
    use WithPagination;

    public ?int $poolId = null;

    public function checkoutEntry(int $entryId): void
    {
        $entry = PoolEntry::query()
            ->where('id', $entryId)
            ->whereNull('exited_at')
            ->firstOrFail();

        $entry->update([
            'exited_at' => now(),
            'exited_by_user_id' => auth()->id(),
        ]);

        session()->flash('message', 'Salida registrada.');

        // refresh
        $this->resetPage();
    }

    public function render()
    {
        $pools = Pool::query()->orderBy('name')->get();

        $baseQuery = PoolEntry::query()
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->when($this->poolId, fn ($q) => $q->where('pool_id', $this->poolId));

        // Metrics (global, not paginated)
        $openEntriesCount = (int) (clone $baseQuery)->count();
        $openGuestsCount = (int) (clone $baseQuery)->sum('guests_count');
        $totalPeopleCount = $openEntriesCount + $openGuestsCount;

        $residentMinorsCount = (int) (clone $baseQuery)
            ->whereNotNull('resident_id')
            ->whereHas('resident', function ($q) {
                $q->whereNotNull('birth_date')
                    ->where('birth_date', '>', now()->subYears(18)->toDateString());
            })
            ->count();

        $guestMinorsCount = (int) DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->join('pool_guests', 'pool_guests.id', '=', 'pool_entry_guests.pool_guest_id')
            ->whereDate('pool_entries.entered_at', now()->toDateString())
            ->whereNull('pool_entries.exited_at')
            ->when($this->poolId, fn ($q) => $q->where('pool_entries.pool_id', $this->poolId))
            ->whereNotNull('pool_guests.birth_date')
            ->where('pool_guests.birth_date', '>', now()->subYears(18)->toDateString())
            ->distinct('pool_guests.id')
            ->count('pool_guests.id');

        $minorsCount = $residentMinorsCount + $guestMinorsCount;

        $entries = (clone $baseQuery)
            ->with(['pool', 'unit.building.complex', 'user', 'resident', 'guests'])
            ->latest('entered_at')
            ->paginate(25);

        return view('livewire.banero.pools.inside', [
            'pools' => $pools,
            'entries' => $entries,
            'totalPeopleCount' => $totalPeopleCount,
            'minorsCount' => $minorsCount,
            'openEntriesCount' => $openEntriesCount,
            'openGuestsCount' => $openGuestsCount,
        ])->layout('components.layouts.banero', ['title' => 'En pileta']);
    }
}
