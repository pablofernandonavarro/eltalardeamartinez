<?php

namespace App\Livewire\Admin\Residents;

use App\Models\Resident;
use App\Models\Unit;
use App\Services\RuleEvaluationService;
use Livewire\Component;

class Create extends Component
{
    public int $unitId = 0;

    public ?int $userId = null;

    public string $name = '';

    public ?string $documentType = null;

    public ?string $documentNumber = null;

    public ?string $birthDate = null;

    public ?string $relationship = null;

    public ?string $startedAt = null;

    public ?string $endedAt = null;

    public ?string $notes = null;

    public function mount(?int $unit_id = null): void
    {
        $this->startedAt = now()->format('Y-m-d');

        if ($unit_id) {
            $this->unitId = $unit_id;
        }
    }

    public function updatedUnitId(): void
    {
        // Resetear userId cuando cambia la unidad para evitar selecciones inválidas
        $this->userId = null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'unitId' => 'required|exists:units,id',
            'userId' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->unitId) {
                        $hasRelation = \App\Models\UnitUser::where('unit_id', $this->unitId)
                            ->where('user_id', $value)
                            ->whereNull('ended_at')
                            ->whereNull('deleted_at')
                            ->exists();

                        if (! $hasRelation) {
                            $fail('El usuario responsable debe tener una relación activa con la unidad funcional seleccionada.');
                        }
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'documentType' => 'nullable|string|max:50',
            'documentNumber' => 'nullable|string|max:50',
            'birthDate' => 'nullable|date|before:today',
            'relationship' => 'nullable|string|max:100',
            'startedAt' => 'nullable|date',
            'endedAt' => 'nullable|date|after:startedAt',
            'notes' => 'nullable|string',
        ], [
            'unitId.required' => 'La unidad funcional es obligatoria.',
            'unitId.exists' => 'La unidad funcional seleccionada no existe.',
            'userId.exists' => 'El usuario responsable seleccionado no existe.',
            'name.required' => 'El nombre es obligatorio.',
            'birthDate.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birthDate.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'startedAt.date' => 'La fecha de inicio debe ser una fecha válida.',
            'endedAt.date' => 'La fecha de fin debe ser una fecha válida.',
            'endedAt.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        // Validar reglas de ocupación antes de crear el residente
        $unit = Unit::findOrFail($validated['unitId']);
        $ruleService = app(RuleEvaluationService::class);

        // Contar habitantes actuales (usuarios + residentes activos)
        $currentOccupantsCount = $ruleService->countCurrentOccupants($unit);

        // Validar que agregar este residente no exceda el límite
        $occupancyCheck = $ruleService->evaluateUnitOccupancyRules($unit, $currentOccupantsCount + 1);

        if (! $occupancyCheck['is_valid']) {
            session()->flash('error', $occupancyCheck['violations'][0]['message']);

            return;
        }

        Resident::create([
            'unit_id' => $validated['unitId'],
            'user_id' => $validated['userId'],
            'name' => $validated['name'],
            'document_type' => $validated['documentType'],
            'document_number' => $validated['documentNumber'],
            'birth_date' => $validated['birthDate'],
            'relationship' => $validated['relationship'],
            'started_at' => $validated['startedAt'],
            'ended_at' => $validated['endedAt'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Residente creado correctamente.');
        $this->redirect(route('admin.residents.index'));
    }

    public function render()
    {
        $units = Unit::query()
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

        // Obtener usuarios que tienen relación activa con la unidad seleccionada
        $users = collect();
        if ($this->unitId > 0) {
            $unitUsers = \App\Models\UnitUser::where('unit_id', $this->unitId)
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->with('user')
                ->get();

            $users = $unitUsers->map(fn ($unitUser) => $unitUser->user)
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        return view('livewire.admin.residents.create', [
            'units' => $units,
            'users' => $users,
        ])->layout('components.layouts.app', ['title' => 'Crear Residente']);
    }
}
