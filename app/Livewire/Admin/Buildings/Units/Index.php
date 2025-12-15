<?php

namespace App\Livewire\Admin\Buildings\Units;

use App\Models\Building;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Building $building;

    public string $search = '';

    public function mount(Building $building): void
    {
        $this->building = $building;
    }

    public function delete(int $unitId): void
    {
        $unit = Unit::findOrFail($unitId);

        if ($unit->building_id !== $this->building->id) {
            session()->flash('error', 'La unidad no pertenece a este edificio.');

            return;
        }

        $unit->delete();
        session()->flash('message', 'Unidad funcional eliminada correctamente.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function render()
    {
        $units = Unit::query()
            ->where('building_id', $this->building->id)
            ->when($this->search, fn ($q) => $q->where('number', 'like', "%{$this->search}%")
                ->orWhere('floor', 'like', "%{$this->search}%"))
            ->orderBy('number')
            ->paginate(15);

        return view('livewire.admin.buildings.units.index', [
            'units' => $units,
        ])->layout('components.layouts.app', ['title' => "Unidades Funcionales - {$this->building->name}"]);
    }
}
