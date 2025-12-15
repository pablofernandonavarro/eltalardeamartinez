<?php

namespace App\Livewire\Admin\Units;

use App\Models\Unit;
use Livewire\Component;

class Show extends Component
{
    public Unit $unit;

    public function mount(Unit $unit): void
    {
        $this->unit = $unit;
    }

    public function delete(): void
    {
        $this->unit->delete();
        session()->flash('message', 'Unidad funcional eliminada correctamente.');
        $this->redirect(route('admin.units.index'));
    }

    public function render()
    {
        $this->unit->load([
            'building.complex',
            'currentUsers.user',
            'activeResidents.user',
        ]);

        $owner = $this->unit->currentUsers->firstWhere('is_owner', true);
        $tenant = $this->unit->currentUsers->firstWhere('is_owner', false);
        $residents = $this->unit->activeResidents;

        return view('livewire.admin.units.show', [
            'owner' => $owner,
            'tenant' => $tenant,
            'residents' => $residents,
        ])->layout('components.layouts.app', ['title' => "Unidad Funcional - {$this->unit->full_identifier}"]);
    }
}
