<?php

namespace App\Livewire\Admin\Residents;

use App\Models\Resident;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $unitId = null;

    public ?string $status = null; // 'active', 'inactive', null

    public string $search = '';

    public function resetFilters(): void
    {
        $this->reset(['unitId', 'status', 'search']);
        $this->resetPage();
    }

    public function delete(int $residentId): void
    {
        $resident = Resident::findOrFail($residentId);
        $resident->delete();
        session()->flash('message', 'Residente eliminado correctamente.');
    }

    public function render()
    {
        $residents = Resident::query()
            ->with([
                'unit' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'unit.building' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'unit.building.complex' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'user',
            ])
            ->when($this->unitId, fn ($q) => $q->where('unit_id', $this->unitId))
            ->when($this->status === 'active', fn ($q) => $q->whereNull('ended_at'))
            ->when($this->status === 'inactive', fn ($q) => $q->whereNotNull('ended_at'))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('document_number', 'like', "%{$this->search}%"))
            ->latest('created_at')
            ->paginate(15);

        $units = \App\Models\Unit::query()
            ->join('buildings', function ($join) {
                $join->on('units.building_id', '=', 'buildings.id')
                    ->whereNull('buildings.deleted_at');
            })
            ->join('complexes', function ($join) {
                $join->on('buildings.complex_id', '=', 'complexes.id')
                    ->whereNull('complexes.deleted_at');
            })
            ->whereNull('units.deleted_at')
            ->select('units.*')
            ->with([
                'building' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'building.complex' => function ($query) {
                    $query->whereNull('deleted_at');
                },
            ])
            ->orderBy('units.building_id')
            ->orderBy('units.number')
            ->get()
            ->filter(function ($unit) {
                return $unit->building
                    && $unit->building->complex
                    && is_null($unit->building->deleted_at)
                    && is_null($unit->building->complex->deleted_at);
            });

        return view('livewire.admin.residents.index', [
            'residents' => $residents,
            'units' => $units,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Residentes']);
    }
}
