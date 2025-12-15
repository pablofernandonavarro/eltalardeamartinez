<?php

namespace App\Livewire\Admin\UnitUsers;

use App\Models\UnitUser;
use Livewire\Component;

class Show extends Component
{
    public UnitUser $unitUser;

    public function mount(UnitUser $unitUser): void
    {
        $this->unitUser = $unitUser->load([
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
            'unit.currentOwner.user',
            'unit.currentResponsible.user',
            'unit.activeResidents.user',
        ]);
    }

    public function render()
    {
        $unit = $this->unitUser->unit;

        // Obtener propietario de la unidad
        $owner = $unit->currentOwner;

        // Obtener responsable del pago de la unidad
        $responsible = $unit->currentResponsible;

        // Obtener todos los usuarios activos de la unidad
        $activeUsers = $unit->currentUsers()->with('user')->get();

        // Obtener residentes activos de la unidad
        $residents = $unit->activeResidents()->with('user')->get();

        return view('livewire.admin.unit-users.show', [
            'unit' => $unit,
            'owner' => $owner,
            'responsible' => $responsible,
            'activeUsers' => $activeUsers,
            'residents' => $residents,
        ])->layout('components.layouts.app', ['title' => 'Detalle de Asignación Usuario-Unidad']);
    }
}
