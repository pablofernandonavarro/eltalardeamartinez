<?php

namespace App\Livewire\Admin\Pools;

use App\Models\Pool;
use App\Models\PoolEntry;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $poolId = null;

    public string $date;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $pools = Pool::query()->orderBy('name')->get();

        $entries = PoolEntry::query()
            ->with(['pool', 'unit.building.complex', 'user', 'resident', 'exitedBy', 'guests'])
            ->when($this->poolId, fn ($q) => $q->where('pool_id', $this->poolId))
            ->when($this->date, fn ($q) => $q->whereDate('entered_at', $this->date))
            ->latest('entered_at')
            ->paginate(20);

        return view('livewire.admin.pools.index', [
            'pools' => $pools,
            'entries' => $entries,
        ])->layout('components.layouts.app', ['title' => 'Registro de Ingresos a Piletas']);
    }
}
