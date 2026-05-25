<?php

namespace App\Livewire\Admin\Users;

use App\Models\Unit;
use App\Models\UnitUser;
use App\Models\User;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    /** @var array<int, array{is_owner: bool, is_responsible: bool}> keyed by UnitUser id */
    public array $unitUsers = [];

    // Nueva asignación de unidad
    public ?int $newUnitId = null;

    public bool $newIsOwner = false;

    public bool $newIsResponsible = false;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role?->value ?? '';

        foreach ($user->currentUnitUsers()->with('unit.building')->get() as $uu) {
            $this->unitUsers[$uu->id] = [
                'is_owner'       => (bool) $uu->is_owner,
                'is_responsible' => (bool) $uu->is_responsible,
            ];
        }
    }

    public function update(): void
    {
        $this->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,'.$this->user->id,
            'password'  => 'nullable|string|min:8',
            'role'      => 'required|in:'.implode(',', array_column(Role::cases(), 'value')),
            'newUnitId' => 'nullable|exists:units,id',
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.max'       => 'El nombre no puede exceder 255 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email'    => 'El email debe ser válido.',
            'email.unique'   => 'Este email ya está registrado.',
            'password.min'   => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required'  => 'El rol es obligatorio.',
        ]);

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => Role::from($this->role),
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        // Actualizar flags de unidades ya asignadas
        foreach ($this->unitUsers as $unitUserId => $flags) {
            UnitUser::where('id', $unitUserId)
                ->where('user_id', $this->user->id)
                ->update([
                    'is_owner'       => (bool) $flags['is_owner'],
                    'is_responsible' => (bool) $flags['is_responsible'],
                ]);
        }

        // Asignar nueva unidad si se seleccionó una
        if ($this->newUnitId) {
            $alreadyAssigned = UnitUser::where('user_id', $this->user->id)
                ->where('unit_id', $this->newUnitId)
                ->whereNull('ended_at')
                ->exists();

            if (! $alreadyAssigned) {
                UnitUser::create([
                    'unit_id'        => $this->newUnitId,
                    'user_id'        => $this->user->id,
                    'is_owner'       => $this->newIsOwner,
                    'is_responsible' => $this->newIsResponsible,
                    'started_at'     => now(),
                ]);
            }
        }

        session()->flash('message', 'Usuario actualizado correctamente.');
        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        $unitUserRecords = $this->user->currentUnitUsers()->with('unit.building')->get();

        // Unidades disponibles: todas menos las que ya tiene asignadas
        $assignedUnitIds = $unitUserRecords->pluck('unit_id')->toArray();
        $availableUnits = Unit::with('building')
            ->whereNotIn('id', $assignedUnitIds)
            ->orderBy('building_id')
            ->orderBy('number')
            ->get();

        return view('livewire.admin.users.edit', [
            'unitUserRecords' => $unitUserRecords,
            'availableUnits'  => $availableUnits,
        ])->layout('components.layouts.app', ['title' => 'Editar Usuario']);
    }
}
