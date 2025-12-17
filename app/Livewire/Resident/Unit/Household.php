<?php

namespace App\Livewire\Resident\Unit;

use App\Models\Resident;
use App\Models\Unit;
use App\Models\UnitPet;
use App\Models\UnitUser;
use App\Services\RuleEvaluationService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Household extends Component
{
    use WithFileUploads;

    public ?int $unitId = null;

    public ?Unit $unit = null;

    public bool $hasPets = false;

    public bool $canEdit = false;

    // Mascotas (múltiples)
    public string $petType = '';

    public ?string $petName = null;

    public ?string $petNotes = null;

    // Alta rápida de residente
    public string $residentName = '';

    public ?string $residentBirthDate = null;

    public ?string $residentRelationship = null;

    public $residentPhoto;

    public function mount(): void
    {
        $user = auth()->user();

        $unitUsers = $user->currentUnitUsers()
            ->with('unit.building.complex')
            ->get();

        $responsible = $unitUsers->firstWhere('is_responsible', true);
        $first = $unitUsers->first();

        $this->unitId = $responsible?->unit_id ?? $first?->unit_id;

        if ($this->unitId) {
            $this->loadUnit();
        }
    }

    public function updatedUnitId(): void
    {
        $this->loadUnit();
    }

    protected function loadUnit(): void
    {
        $this->resetErrorBag();
        $this->unit = null;
        $this->canEdit = false;
        $this->hasPets = false;

        if (! $this->unitId) {
            return;
        }

        $user = auth()->user();

        $unitUser = UnitUser::query()
            ->where('unit_id', $this->unitId)
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->whereNull('deleted_at')
            ->first();

        if (! $unitUser) {
            abort(403);
        }

        $this->unit = Unit::query()
            ->with('building.complex')
            ->findOrFail($this->unitId);

        $this->hasPets = (bool) $this->unit->has_pets;
        $this->canEdit = (bool) $unitUser->is_responsible;

        // Reset forms
        $this->petType = '';
        $this->petName = null;
        $this->petNotes = null;

        $this->residentName = '';
        $this->residentBirthDate = null;
        $this->residentRelationship = null;
        $this->residentPhoto = null;
    }

    protected function syncHasPetsFlag(): void
    {
        if (! $this->unit) {
            return;
        }

        $hasPets = UnitPet::query()
            ->where('unit_id', $this->unit->id)
            ->whereNull('deleted_at')
            ->exists();

        $this->unit->update(['has_pets' => $hasPets]);
        $this->hasPets = $hasPets;
    }

    public function addPet(): void
    {
        if (! $this->canEdit || ! $this->unit) {
            session()->flash('error', 'Solo el responsable de pago puede cargar mascotas.');

            return;
        }

        $validated = $this->validate([
            'petType' => 'required|string|max:50',
            'petName' => 'nullable|string|max:50',
            'petNotes' => 'nullable|string|max:500',
        ], [
            'petType.required' => 'Debe seleccionar un tipo.',
        ]);

        UnitPet::create([
            'unit_id' => $this->unit->id,
            'type' => $validated['petType'],
            'name' => $validated['petName'],
            'notes' => $validated['petNotes'],
        ]);

        $this->petType = '';
        $this->petName = null;
        $this->petNotes = null;

        $this->syncHasPetsFlag();

        session()->flash('message', 'Mascota agregada.');
    }

    public function removePet(int $petId): void
    {
        if (! $this->canEdit || ! $this->unit) {
            session()->flash('error', 'Solo el responsable de pago puede eliminar mascotas.');

            return;
        }

        $pet = UnitPet::query()
            ->where('id', $petId)
            ->where('unit_id', $this->unit->id)
            ->firstOrFail();

        $pet->delete();

        $this->syncHasPetsFlag();

        session()->flash('message', 'Mascota eliminada.');
    }

    public function addResident(): void
    {
        if (! $this->canEdit || ! $this->unit) {
            session()->flash('error', 'Solo el responsable de pago puede cargar residentes.');

            return;
        }

        $validated = $this->validate([
            'residentName' => 'required|string|max:255',
            'residentBirthDate' => 'nullable|date|before:today',
            'residentRelationship' => 'nullable|string|max:100',
            'residentPhoto' => 'nullable|image|max:2048',
        ], [
            'residentName.required' => 'El nombre es obligatorio.',
        ]);

        // Validar reglas de ocupación
        $ruleService = app(RuleEvaluationService::class);
        $currentOccupantsCount = $ruleService->countCurrentOccupants($this->unit);
        $occupancyCheck = $ruleService->evaluateUnitOccupancyRules($this->unit, $currentOccupantsCount + 1);

        if (! $occupancyCheck['is_valid']) {
            session()->flash('error', $occupancyCheck['violations'][0]['message']);

            return;
        }

        $photoPath = null;
        if ($this->residentPhoto) {
            $photoPath = $this->residentPhoto->store('residents', 'public');
        }

        Resident::create([
            'unit_id' => $this->unit->id,
            'user_id' => auth()->id(),
            'name' => $validated['residentName'],
            'profile_photo_path' => $photoPath,
            'birth_date' => $validated['residentBirthDate'],
            'relationship' => $validated['residentRelationship'],
            'started_at' => now()->toDateString(),
            'ended_at' => null,
        ]);

        session()->flash('message', 'Residente agregado.');

        $this->residentName = '';
        $this->residentBirthDate = null;
        $this->residentRelationship = null;
        $this->residentPhoto = null;
    }

    public function finishResident(int $residentId): void
    {
        if (! $this->canEdit || ! $this->unit) {
            session()->flash('error', 'Solo el responsable de pago puede finalizar residentes.');

            return;
        }

        $resident = Resident::query()
            ->where('id', $residentId)
            ->where('unit_id', $this->unit->id)
            ->firstOrFail();

        $resident->update([
            'ended_at' => now()->toDateString(),
        ]);

        session()->flash('message', 'Residente finalizado.');
    }

    public function render()
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->with('unit.building.complex')->get();

        $residents = collect();
        $pets = collect();

        if ($this->unitId) {
            $residents = Resident::query()
                ->where('unit_id', $this->unitId)
                ->active()
                ->orderBy('name')
                ->get();

            $pets = UnitPet::query()
                ->where('unit_id', $this->unitId)
                ->orderBy('type')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.resident.unit.household', [
            'unitUsers' => $unitUsers,
            'residents' => $residents,
            'pets' => $pets,
        ])->layout('components.layouts.resident', ['title' => 'Mi Hogar']);
    }
}
