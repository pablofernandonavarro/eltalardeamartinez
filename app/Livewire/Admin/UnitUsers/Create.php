<?php

namespace App\Livewire\Admin\UnitUsers;

use App\Models\Unit;
use App\Models\UnitUser;
use App\Models\User;
use Livewire\Component;

class Create extends Component
{
    public int $userId = 0;

    public int $unitId = 0;

    public bool $isOwner = false;

    public bool $isResponsible = false;

    public string $startedAt = '';

    public ?string $endedAt = null;

    public ?string $notes = null;

    public function mount(?int $unit_id = null): void
    {
        $this->startedAt = now()->format('Y-m-d');

        if ($unit_id) {
            $this->unitId = $unit_id;
        }
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
        if ($validated['isOwner']) {
            if ($user->role === null) {
                session()->flash('error', 'El usuario seleccionado no tiene un rol asignado. Debe asignarle el rol "Propietario" antes de marcarlo como propietario de una unidad.');

                return;
            }

            if ($user->role !== \App\Role::Propietario) {
                session()->flash('error', 'Solo los usuarios con rol Propietario pueden ser propietarios de una unidad. El usuario seleccionado tiene rol: '.($user->role?->label() ?? 'Sin rol').'.');

                return;
            }
        }

        // Verificar si ya existe una relación activa para este usuario y unidad
        $existingActive = UnitUser::where('user_id', $validated['userId'])
            ->where('unit_id', $validated['unitId'])
            ->whereNull('ended_at')
            ->whereNull('deleted_at')
            ->exists();

        if ($existingActive) {
            session()->flash('error', 'Ya existe una relación activa entre este usuario y esta unidad funcional.');

            return;
        }

        // Si es propietario, verificar que no haya otro propietario activo para esta unidad
        if ($validated['isOwner']) {
            $existingOwner = UnitUser::where('unit_id', $validated['unitId'])
                ->where('is_owner', true)
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->exists();

            if ($existingOwner) {
                session()->flash('error', 'Esta unidad funcional ya tiene un propietario activo. Una unidad solo puede tener un propietario a la vez.');

                return;
            }
        }

        // Si es inquilino (no propietario), verificar que no haya otro inquilino activo para esta unidad
        // Solo validar si el usuario tiene rol Inquilino
        if (! $validated['isOwner'] && $user->role === \App\Role::Inquilino) {
            $existingTenant = UnitUser::where('unit_id', $validated['unitId'])
                ->where('is_owner', false)
                ->where('user_id', '!=', $validated['userId']) // Excluir el usuario actual
                ->whereHas('user', function ($q) {
                    $q->where('role', \App\Role::Inquilino);
                })
                ->whereNull('ended_at')
                ->whereNull('deleted_at')
                ->exists();

            if ($existingTenant) {
                session()->flash('error', 'Esta unidad funcional ya tiene un inquilino activo. Una unidad solo puede tener un inquilino a la vez.');

                return;
            }
        }

        UnitUser::create([
            'user_id' => $validated['userId'],
            'unit_id' => $validated['unitId'],
            'is_owner' => $validated['isOwner'],
            'is_responsible' => $validated['isResponsible'],
            'started_at' => $validated['startedAt'],
            'ended_at' => $validated['endedAt'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Relación creada correctamente.');
        $this->redirect(route('admin.unit-users.index'));
    }

    public function render()
    {
        // Mostrar usuarios con rol Propietario o Inquilino, o sin rol (para que el admin pueda asignarles rol primero)
        $users = User::where(function ($query) {
            $query->whereIn('role', [\App\Role::Propietario, \App\Role::Inquilino])
                ->orWhereNull('role');
        })
            ->orderByRaw('CASE WHEN role IS NULL THEN 1 ELSE 0 END') // Usuarios sin rol al final
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

        return view('livewire.admin.unit-users.create', [
            'users' => $users,
            'units' => $units,
        ])->layout('components.layouts.app', ['title' => 'Asignar Usuario a Unidad Funcional']);
    }
}
