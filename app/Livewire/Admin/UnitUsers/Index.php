<?php

namespace App\Livewire\Admin\UnitUsers;

use App\Models\UnitUser;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $userId = null;

    public ?int $unitId = null;

    public ?string $status = null; // 'active', 'inactive', null

    public function resetFilters(): void
    {
        $this->reset(['userId', 'unitId', 'status']);
        $this->resetPage();
    }

    public function delete(int $unitUserId): void
    {
        $unitUser = UnitUser::findOrFail($unitUserId);
        $unitUser->delete();
        session()->flash('message', 'Relación eliminada correctamente.');
    }

    public function render()
    {
        $unitUsers = UnitUser::query()
            ->whereHas('unit', function ($query) {
                $query->whereNull('deleted_at')
                    ->whereHas('building', function ($query) {
                        $query->whereNull('deleted_at')
                            ->whereHas('complex', function ($query) {
                                $query->whereNull('deleted_at');
                            });
                    });
            })
            ->with([
                'user',
                'unit' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'unit.building' => function ($query) {
                    $query->whereNull('deleted_at');
                },
                'unit.building.complex' => function ($query) {
                    $query->whereNull('deleted_at');
                },
            ])
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->when($this->unitId, fn ($q) => $q->where('unit_id', $this->unitId))
            ->when($this->status === 'active', fn ($q) => $q->whereNull('ended_at'))
            ->when($this->status === 'inactive', fn ($q) => $q->whereNotNull('ended_at'))
            ->latest('started_at')
            ->paginate(15);

        $users = \App\Models\User::whereIn('role', [\App\Role::Propietario, \App\Role::Inquilino])
            ->orderBy('name')
            ->get();

        $units = \App\Models\Unit::query()
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

        return view('livewire.admin.unit-users.index', [
            'unitUsers' => $unitUsers,
            'users' => $users,
            'units' => $units,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Unidades Funcionales']);
    }
}
