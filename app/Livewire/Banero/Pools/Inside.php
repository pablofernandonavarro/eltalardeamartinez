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

    public ?\App\Models\PoolShift $activeShift = null;

    protected $listeners = ['entry-registered' => '$refresh'];

    public function mount(): void
    {
        $this->activeShift = \App\Models\PoolShift::getActiveShiftForUser(auth()->id());

        if (! $this->activeShift) {
            session()->flash('error', 'Debes iniciar tu turno antes de ver quién está en la pileta.');
            $this->redirect(route('banero.my-shift'), navigate: true);
        }

        // Asignar automáticamente la pileta del turno activo
        $this->poolId = $this->activeShift->pool_id;
    }

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
        // Solo mostrar la pileta del turno activo
        $pool = $this->activeShift ? Pool::find($this->activeShift->pool_id) : null;

        $baseQuery = PoolEntry::query()
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->where('pool_id', $this->poolId);
        
        \Log::info('📊 Inside - Consulta base', [
            'pool_id' => $this->poolId,
            'date' => now()->toDateString(),
            'timezone' => config('app.timezone'),
            'now' => now()->toDateTimeString(),
        ]);

        // Metrics (global, not paginated)
        $openEntriesCount = (int) (clone $baseQuery)->count();
        
        \Log::info('📊 Inside - Resultado', [
            'open_entries_count' => $openEntriesCount,
            'all_today_entries' => PoolEntry::whereDate('entered_at', now()->toDateString())->where('pool_id', $this->poolId)->count(),
            'all_today_with_exit' => PoolEntry::whereDate('entered_at', now()->toDateString())->where('pool_id', $this->poolId)->whereNotNull('exited_at')->count(),
        ]);
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
            'pool' => $pool,
            'entries' => $entries,
            'totalPeopleCount' => $totalPeopleCount,
            'minorsCount' => $minorsCount,
            'openEntriesCount' => $openEntriesCount,
            'openGuestsCount' => $openGuestsCount,
        ])->layout('components.layouts.banero', ['title' => 'En pileta']);
    }
}
