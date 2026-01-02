<?php

namespace App\Livewire\Banero;

use App\Models\Pool;
use App\Models\PoolShift;
use Livewire\Component;

class MyShift extends Component
{
    public ?PoolShift $activeShift = null;

    public ?int $selectedPoolId = null;

    public function mount(): void
    {
        $this->loadActiveShift();
    }

    protected function loadActiveShift(): void
    {
        $this->activeShift = PoolShift::getActiveShiftForUser(auth()->id());
        $this->selectedPoolId = $this->activeShift?->pool_id;
    }

    public function startShift(): void
    {
        $this->validate([
            'selectedPoolId' => 'required|exists:pools,id',
        ], [
            'selectedPoolId.required' => 'Debe seleccionar una pileta.',
        ]);

        // Verificar que el bañero no tenga turno activo
        if (! PoolShift::canStartShift(auth()->id())) {
            $this->addError('error', 'Ya tenés un turno activo. Finalizalo antes de iniciar uno nuevo.');

            return;
        }

        // Verificar que la pileta no tenga otro bañero activo
        $existingShift = PoolShift::getActiveShiftForPool($this->selectedPoolId);
        if ($existingShift) {
            $this->addError('error', "Ya hay un bañero en turno en esta pileta ({$existingShift->user->name}).");

            return;
        }

        // Crear nuevo turno
        PoolShift::create([
            'pool_id' => $this->selectedPoolId,
            'user_id' => auth()->id(),
            'started_at' => now(),
        ]);

        session()->flash('message', 'Turno iniciado correctamente.');

        $this->loadActiveShift();
    }

    public function endShift(): void
    {
        if (! $this->activeShift) {
            $this->addError('error', 'No tenés un turno activo.');

            return;
        }

        $this->activeShift->end();

        session()->flash('message', 'Turno finalizado correctamente.');

        $this->loadActiveShift();
    }

    public function render()
    {
        $pools = Pool::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('livewire.banero.my-shift', [
            'pools' => $pools,
        ])->layout('components.layouts.banero', ['title' => 'Mi Turno']);
    }
}
