<?php

namespace App\Livewire\Resident\Pools\Guests;

use App\Models\PoolGuest;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public PoolGuest $guest;

    public $photo;

    public int $unitId;

    public string $name;

    public ?string $birthDate;

    public ?string $documentType;

    public ?string $documentNumber;

    public ?string $phone;

    public ?string $notes;

    public function mount(PoolGuest $guest): void
    {
        $user = auth()->user();
        $allowedUnitIds = $user->currentUnitUsers()->pluck('unit_id')->all();

        if ($guest->created_by_user_id !== $user->id || ! in_array($guest->unit_id, $allowedUnitIds, true)) {
            abort(403);
        }

        $this->guest = $guest;
        $this->unitId = $guest->unit_id;
        $this->name = $guest->name;
        $this->birthDate = $guest->birth_date?->format('Y-m-d');
        $this->documentType = $guest->document_type;
        $this->documentNumber = $guest->document_number;
        $this->phone = $guest->phone;
        $this->notes = $guest->notes;
    }

    public function update(): void
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
        ]);

        if (! in_array((int) $validated['unitId'], $allowedUnitIds, true)) {
            abort(403);
        }

        $data = [
            'unit_id' => $validated['unitId'],
            'name' => $validated['name'],
            'birth_date' => $validated['birthDate'],
            'document_type' => $validated['documentType'],
            'document_number' => $validated['documentNumber'],
            'phone' => $validated['phone'],
            'notes' => $validated['notes'],
        ];

        if ($this->photo) {
            $data['profile_photo_path'] = $this->photo->store('pool-guests', 'public');
        }

        $this->guest->update($data);

        session()->flash('message', 'Invitado actualizado.');
        $this->redirect(route('resident.pools.guests.index'));
    }

    public function render()
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->with('unit.building.complex')->get();

        return view('livewire.resident.pools.guests.edit', [
            'unitUsers' => $unitUsers,
            'guest' => $this->guest,
        ])->layout('components.layouts.resident', ['title' => 'Editar invitado']);
    }
}
