<?php

namespace App\Livewire\Admin\UnitUsers;

use App\Models\Unit;
use App\Models\UnitUser;
use App\Models\User;
use Livewire\Component;

class Edit extends Component
{
    public UnitUser $unitUser;

    public int $userId = 0;

    public int $unitId = 0;

    public bool $isOwner = false;

    public bool $isResponsible = false;

    public string $startedAt = '';

    public ?string $endedAt = null;

    public ?string $notes = null;

    public function mount(UnitUser $unitUser): void
    {
        $this->unitUser = $unitUser;
        $this->userId = $unitUser->user_id;
        $this->unitId = $unitUser->unit_id;
        $this->isOwner = $unitUser->is_owner ?? false;
        $this->isResponsible = $unitUser->is_responsible;
        $this->startedAt = $unitUser->started_at->format('Y-m-d');
        $this->endedAt = $unitUser->ended_at?->format('Y-m-d');
        $this->notes = $unitUser->notes;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'userId' => 'required|exists:users,id',
            'unitId' => 'required|exists:units,id',
            'isOwner' => 'boolean',
            'isResponsible' => 'boolean',
            'startedAt' => 'required|date',
            'endedAt' => 'nullable|date|after:startedAt',
            'notes' => 'nullable|string',
        ], [
            'userId.required' => 'El usuario es obligatorio.',
            'userId.exists' => 'El usuario seleccionado no existe.',
            'unitId.required' => 'La unidad funcional es obligatoria.',
            'unitId.exists' => 'La unidad funcional seleccionada no existe.',
            'startedAt.required' => 'La fecha de inicio es obligatoria.',
            'startedAt.date' => 'La fecha de inicio debe ser una fecha válida.',
            'endedAt.date' => 'La fecha de fin debe ser una fecha válida.',
            'endedAt.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        $user = User::findOrFail($validated['userId']);

        // Validar que si es propietario, el usuario debe tener rol Propietario
        if ($validated['isOwner'] && $user->role !== \App\Role::Propietario) {
            session()->flash('error', 'Solo los usuarios con rol Propietario pueden ser propietarios de una unidad.');

            return;
        }

        // Verificar si ya existe otra relación activa para este usuario y unidad (excluyendo la actual)
        $existingActive = UnitUser::where('user_id', $validated['userId'])
            ->where('unit_id', $validated['unitId'])
            ->where('id', '!=', $this->unitUser->id)
            ->whereNull('ended_at')
            ->whereNull('deleted_at')
            ->exists();

        if ($existingActive && ! $validated['endedAt']) {
            session()->flash('error', 'Ya existe otra relación activa entre este usuario y esta unidad funcional.');

            return;
        }

        // Si está marcando como propietario, verificar que no haya otro propietario activo para esta unidad (excluyendo la actual)
        if ($validated['isOwner']) {
            $existingOwner = UnitUser::where('unit_id', $validated['unitId'])
                ->where('is_owner', true)
                ->where('id', '!=', $this->unitUser->id)
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->exists();

            if ($existingOwner) {
                session()->flash('error', 'Esta unidad funcional ya tiene otro propietario activo.');

                return;
            }
        }

        // Si está marcando como inquilino (no propietario), verificar que no haya otro inquilino activo para esta unidad (excluyendo la actual)
        if (! $validated['isOwner'] && $user->role === \App\Role::Inquilino && ! $validated['endedAt']) {
            $existingTenant = UnitUser::where('unit_id', $validated['unitId'])
                ->where('is_owner', false)
                ->where('id', '!=', $this->unitUser->id)
                ->whereHas('user', function ($q) {
                    $q->where('role', \App\Role::Inquilino);
                })
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->exists();

            if ($existingTenant) {
                session()->flash('error', 'Esta unidad funcional ya tiene otro inquilino activo.');

                return;
            }
        }

        // Si se marca como responsable del pago, verificar que no haya otro responsable activo (excluyendo la actual)
        if ($validated['isResponsible'] && ! $validated['endedAt']) {
            $existingResponsible = UnitUser::where('unit_id', $validated['unitId'])
                ->where('is_responsible', true)
                ->where('id', '!=', $this->unitUser->id)
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->exists();

            if ($existingResponsible) {
                session()->flash('error', 'Esta unidad funcional ya tiene un responsable de pago activo. Solo puede haber un responsable de pago por unidad.');

                return;
            }
        }

        $this->unitUser->update([
            'user_id' => $validated['userId'],
            'unit_id' => $validated['unitId'],
            'is_owner' => $validated['isOwner'],
            'is_responsible' => $validated['isResponsible'],
            'started_at' => $validated['startedAt'],
            'ended_at' => $validated['endedAt'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Relación actualizada correctamente.');
        $this->redirect(route('admin.unit-users.index'));
    }

    public function render()
    {
        $users = User::whereIn('role', [\App\Role::Propietario, \App\Role::Inquilino])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->whereNull('units.deleted_at')
            ->whereHas('building', function ($query) {
                $query->whereNull('deleted_at')
                    ->whereHas('complex', function ($query) {
                        $query->whereNull('deleted_at');
                    });
            })
            ->with([
                'building' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'building.complex' => function ($query) {
                    $query->whereNull('deleted_at');
                },
            ])
            ->orderBy('building_id')
            ->orderBy('number')
            ->get();

        return view('livewire.admin.unit-users.edit', [
            'users' => $users,
            'units' => $units,
        ])->layout('components.layouts.app', ['title' => 'Editar Relación Usuario-Unidad']);
    }
}
