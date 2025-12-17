<?php

namespace App\Livewire\Resident\Pools\Guests;

use App\Models\PoolGuest;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $unitId = null;

    public string $name = '';

    public $photo;

    public ?string $birthDate = null;

    public ?string $documentType = null;

    public ?string $documentNumber = null;

    public ?string $phone = null;

    public ?string $notes = null;

    public function mount(): void
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->pluck('unit_id')->all();
        $this->unitId = $units[0] ?? null;
    }

    public function save(): void
    {
        $user = auth()->user();
        $allowedUnitIds = $user->currentUnitUsers()->pluck('unit_id')->all();

        $validated = $this->validate([
            'unitId' => 'required|integer',
            'name' => 'required|string|max:255',
            'birthDate' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
            'documentType' => 'nullable|string|max:50',
            'documentNumber' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ], [
            'unitId.required' => 'Debe seleccionar una unidad.',
            'name.required' => 'El nombre es obligatorio.',
        ]);

        if (! in_array((int) $validated['unitId'], $allowedUnitIds, true)) {
            abort(403);
        }

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('pool-guests', 'public');
        }

        PoolGuest::create([
            'unit_id' => $validated['unitId'],
            'created_by_user_id' => $user->id,
            'name' => $validated['name'],
            'birth_date' => $validated['birthDate'],
            'profile_photo_path' => $photoPath,
            'document_type' => $validated['documentType'],
            'document_number' => $validated['documentNumber'],
            'phone' => $validated['phone'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Invitado creado.');
        $this->redirect(route('resident.pools.guests.index'));
    }

    public function render()
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->with('unit.building.complex')->get();

        return view('livewire.resident.pools.guests.create', [
            'unitUsers' => $unitUsers,
        ])->layout('components.layouts.resident', ['title' => 'Nuevo invitado']);
    }
}
