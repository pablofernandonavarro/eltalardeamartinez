<?php

namespace App\Livewire\Admin\Buildings;

use App\Models\Building;
use App\Models\Complex;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $complexId = null;

    public string $search = '';

    public function resetFilters(): void
    {
        $this->reset(['complexId', 'search']);
        $this->resetPage();
    }

    public function delete(int $buildingId): void
    {
        $building = Building::findOrFail($buildingId);
        $building->delete();
        session()->flash('message', 'Edificio eliminado correctamente.');
    }

    public function render()
    {
        $complexes = Complex::all();

        $buildings = Building::query()
            ->with(['complex', 'units'])
            ->when($this->complexId, fn ($q) => $q->where('complex_id', $this->complexId))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('address', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.buildings.index', [
            'complexes' => $complexes,
            'buildings' => $buildings,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Edificios']);
    }
}
