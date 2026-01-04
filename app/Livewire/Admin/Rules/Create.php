<?php

namespace App\Livewire\Admin\Rules;

use App\Http\Requests\Admin\RuleRequest;
use App\Models\SystemRule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $type = 'unit_occupancy';

    public string $name = '';

    public ?string $description = null;

    public array $conditions = [];

    public array $limits = [];

    public bool $is_active = true;

    public ?string $valid_from = null;

    public ?string $valid_to = null;

    public int $priority = 0;

    public ?string $notes = null;

    public $document;

    public function mount(): void
    {
        $this->valid_from = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate((new RuleRequest)->rules());

        // Validar documento si fue subido
        if ($this->document) {
            $this->validate([
                'document' => 'file|mimes:pdf|max:10240', // 10MB max
            ], [
                'document.mimes' => 'El documento debe ser un archivo PDF.',
                'document.max' => 'El documento no puede superar los 10MB.',
            ]);
        }

        $documentPath = null;
        if ($this->document) {
            $documentPath = $this->document->store('documents', 'public');
        }

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
            'document_path' => $documentPath,
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
