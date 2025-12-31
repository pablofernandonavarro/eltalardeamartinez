<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?string $role = null;

    public ?string $approvalStatus = null; // 'approved', 'pending', null

    public string $search = '';

    public function resetFilters(): void
    {
        $this->reset(['role', 'approvalStatus', 'search']);
        $this->resetPage();
    }

    public function approve(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->approve();

        // Si el usuario tiene una unidad solicitada, crear la asignación
        if ($user->requested_unit_id) {
            \App\Models\UnitUser::create([
                'unit_id' => $user->requested_unit_id,
                'user_id' => $user->id,
                'is_owner' => false,
                'is_responsible' => false,
                'started_at' => now(),
            ]);
        }

        session()->flash('message', 'Usuario aprobado correctamente y asignado a su unidad.');
    }

    public function reject(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->isAdmin()) {
            session()->flash('error', 'No se puede rechazar un administrador.');

            return;
        }

        $user->reject();
        session()->flash('message', 'Usuario rechazado correctamente.');
    }

    public function delete(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');

            return;
        }

        $user->delete();
        session()->flash('message', 'Usuario eliminado correctamente.');
    }

    public function render()
    {
        $users = User::query()
            ->with(['currentUnitUsers.unit.building.complex', 'requestedUnit'])
            ->when($this->role === 'null', fn ($q) => $q->whereNull('role'))
            ->when($this->role && $this->role !== 'null', fn ($q) => $q->where('role', $this->role))
            ->when($this->approvalStatus === 'approved', function ($q) {
                $q->where(function ($query) {
                    $query->whereNotNull('approved_at')
                        ->orWhere('role', \App\Role::Admin);
                });
            })
            ->when($this->approvalStatus === 'pending', function ($q) {
                $q->whereNull('approved_at')
                    ->where(function ($query) {
                        $query->whereNull('role')
                            ->orWhere('role', '!=', \App\Role::Admin);
                    });
            })
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $users,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Usuarios']);
    }
}
