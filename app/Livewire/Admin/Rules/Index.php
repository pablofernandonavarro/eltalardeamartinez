<?php

namespace App\Livewire\Admin\Rules;

use App\Models\SystemRule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?string $type = null;

    public ?string $status = null; // 'active', 'inactive', null

    public string $search = '';

    public function resetFilters(): void
    {
        $this->reset(['type', 'status', 'search']);
        $this->resetPage();
    }

    public function delete(int $ruleId): void
    {
        $rule = SystemRule::findOrFail($ruleId);
        $rule->delete();
        session()->flash('message', 'Regla eliminada correctamente.');
    }

    public function toggleActive(int $ruleId): void
    {
        $rule = SystemRule::findOrFail($ruleId);
        $rule->update(['is_active' => ! $rule->is_active]);
        session()->flash('message', 'Estado de la regla actualizado correctamente.');
    }

    public function render()
    {
        $rules = SystemRule::query()
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            }))
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $ruleTypes = [
            'unit_occupancy' => 'Ocupación de Unidades',
            'pool_weekly_guests' => 'Invitados Semanales (Piletas)',
            'pool_monthly_guests' => 'Invitados Mensuales (Piletas)',
        ];

        return view('livewire.admin.rules.index', [
            'rules' => $rules,
            'ruleTypes' => $ruleTypes,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Reglas del Sistema']);
    }
}
