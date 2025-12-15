<?php

namespace App\Livewire\Admin\Units;

use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $buildingId = null;

    public ?int $complexId = null;

    public string $search = '';

    public function resetFilters(): void
    {
        $this->reset(['buildingId', 'complexId', 'search']);
        $this->resetPage();
    }

    public function render()
    {
        $units = Unit::query()
            ->whereNull('units.deleted_at')
            ->whereHas('building', function ($query) {
                $query->whereNull('deleted_at')
                    ->when($this->complexId, function ($q) {
                        $q->where('complex_id', $this->complexId);
                    })
                    ->when($this->buildingId, function ($q) {
                        $q->where('id', $this->buildingId);
                    });
            })
            ->with([
                'building' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'building.complex' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'currentUsers.user',
                'residents' => function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('ended_at')
                            ->orWhere('ended_at', '>', now());
                    })
                        ->whereNull('deleted_at');
                },
            ])
            ->when($this->search, function ($q) {
                $q->where('number', 'like', "%{$this->search}%")
                    ->orWhere('floor', 'like', "%{$this->search}%");
            })
            ->orderBy('building_id')
            ->orderBy('number')
            ->paginate(15);

        $buildings = \App\Models\Building::whereNull('deleted_at')
            ->when($this->complexId, function ($q) {
                $q->where('complex_id', $this->complexId);
            })
            ->orderBy('name')
            ->get();

        $complexes = \App\Models\Complex::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.units.index', [
            'units' => $units,
            'buildings' => $buildings,
            'complexes' => $complexes,
        ])->layout('components.layouts.app', ['title' => 'Unidades Funcionales']);
    }
}
