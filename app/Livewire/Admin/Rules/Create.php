<?php

namespace App\Livewire\Admin\Rules;

use App\Http\Requests\Admin\RuleRequest;
use App\Models\SystemRule;
use Livewire\Component;

class Create extends Component
{
    public string $type = 'unit_occupancy';

    public string $name = '';

    public ?string $description = null;

    public array $conditions = [];

    public array $limits = [];

    public bool $isActive = true;

    public ?string $validFrom = null;

    public ?string $validTo = null;

    public int $priority = 0;

    public ?string $notes = null;

    public function mount(): void
    {
        $this->validFrom = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate((new RuleRequest())->rules());

        SystemRule::create([
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

        session()->flash('message', 'Regla creada correctamente.');
        $this->redirect(route('admin.rules.index'));
    }

    public function render()
    {
        $ruleTypes = [
            'unit_occupancy' => 'Ocupación de Unidades',
            'pool_weekly_guests' => 'Invitados Semanales (Piletas)',
            'pool_monthly_guests' => 'Invitados Mensuales (Piletas)',
        ];

        return view('livewire.admin.rules.create', [
            'ruleTypes' => $ruleTypes,
        ])->layout('components.layouts.app', ['title' => 'Crear Regla del Sistema']);
    }
}
