<?php

namespace App\Livewire\Admin\Rules;

use App\Http\Requests\Admin\RuleRequest;
use App\Models\SystemRule;
use Livewire\Component;

class Edit extends Component
{
    public SystemRule $rule;

    public string $type = '';

    public string $name = '';

    public ?string $description = null;

    public array $conditions = [];

    public array $limits = [];

    public bool $isActive = true;

    public ?string $validFrom = null;

    public ?string $validTo = null;

    public int $priority = 0;

    public ?string $notes = null;

    public function mount(SystemRule $rule): void
    {
        $this->rule = $rule;
        $this->type = $rule->type;
        $this->name = $rule->name;
        $this->description = $rule->description;
        $this->conditions = $rule->conditions ?? [];
        $this->limits = $rule->limits ?? [];
        $this->isActive = $rule->is_active;
        $this->validFrom = $rule->valid_from?->format('Y-m-d');
        $this->validTo = $rule->valid_to?->format('Y-m-d');
        $this->priority = $rule->priority;
        $this->notes = $rule->notes;
    }

    public function save(): void
    {
        $validated = $this->validate((new RuleRequest())->rules());

        $this->rule->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'conditions' => $validated['conditions'] ?? [],
            'limits' => $validated['limits'],
            'is_active' => $validated['is_active'] ?? true,
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
            'priority' => $validated['priority'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Regla actualizada correctamente.');
        $this->redirect(route('admin.rules.index'));
    }

    public function render()
    {
        $ruleTypes = [
            'unit_occupancy' => 'Ocupación de Unidades',
            'pool_weekly_guests' => 'Invitados Semanales (Piletas)',
            'pool_monthly_guests' => 'Invitados Mensuales (Piletas)',
        ];

        return view('livewire.admin.rules.edit', [
            'ruleTypes' => $ruleTypes,
        ])->layout('components.layouts.app', ['title' => 'Editar Regla del Sistema']);
    }
}
