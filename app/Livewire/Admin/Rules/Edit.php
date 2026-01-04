<?php

namespace App\Livewire\Admin\Rules;

use App\Http\Requests\Admin\RuleRequest;
use App\Models\SystemRule;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public SystemRule $rule;

    public string $type = '';

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

    public bool $removeDocument = false;

    public function mount(SystemRule $rule): void
    {
        $this->rule = $rule;
        $this->type = $rule->type;
        $this->name = $rule->name;
        $this->description = $rule->description;
        $this->conditions = $rule->conditions ?? [];
        $this->limits = $rule->limits ?? [];
        $this->is_active = $rule->is_active;
        $this->valid_from = $rule->valid_from?->format('Y-m-d');
        $this->valid_to = $rule->valid_to?->format('Y-m-d');
        $this->priority = $rule->priority;
        $this->notes = $rule->notes;
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

        $documentPath = $this->rule->document_path;

        // Si se marca para eliminar el documento
        if ($this->removeDocument && $documentPath) {
            Storage::disk('public')->delete($documentPath);
            $documentPath = null;
        }

        // Si se sube un nuevo documento
        if ($this->document) {
            // Eliminar el documento anterior si existe
            if ($documentPath) {
                Storage::disk('public')->delete($documentPath);
            }
            $documentPath = $this->document->store('documents', 'public');
        }

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
            'document_path' => $documentPath,
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
