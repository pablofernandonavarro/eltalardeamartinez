<?php

namespace App\Livewire\Admin\Expenses;

use App\Models\Building;
use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $buildingId = null;

    public ?string $type = null;

    public string $search = '';

    public function resetFilters(): void
    {
        $this->reset(['buildingId', 'type', 'search']);
        $this->resetPage();
    }

    public function render()
    {
        $buildings = Building::with('complex')->get();

        $expenses = Expense::query()
            ->with(['building.complex', 'concept', 'details.unit'])
            ->when($this->buildingId, fn ($q) => $q->where('building_id', $this->buildingId))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->latest('due_date')
            ->paginate(15);

        return view('livewire.admin.expenses.index', [
            'buildings' => $buildings,
            'expenses' => $expenses,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Expensas']);
    }
}
