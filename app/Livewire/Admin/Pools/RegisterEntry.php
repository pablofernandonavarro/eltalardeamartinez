<?php

namespace App\Livewire\Admin\Pools;

use App\Models\Pool;
use App\Models\Unit;
use App\Models\User;
use App\Role;
use App\Services\PoolAccessService;
use Livewire\Component;

class RegisterEntry extends Component
{
    public ?int $poolId = null;

    public ?int $unitId = null;

    public ?int $userId = null;

    public int $guestsCount = 0;

    public string $enteredAt;

    public function mount(): void
    {
        $this->enteredAt = now()->format('Y-m-d\TH:i');
    }

    public function registerEntry(PoolAccessService $poolAccessService): void
    {
        $this->validate([
            'poolId' => 'required|exists:pools,id',
            'unitId' => 'required|exists:units,id',
            'userId' => 'required|exists:users,id',
            'guestsCount' => 'required|integer|min:0|max:10',
            'enteredAt' => 'required|date',
        ]);

        try {
            $pool = Pool::findOrFail($this->poolId);
            $unit = Unit::findOrFail($this->unitId);
            $user = User::findOrFail($this->userId);

            $poolAccessService->registerEntry($pool, $unit, $user, $this->guestsCount, $this->enteredAt);

            session()->flash('message', 'Ingreso registrado correctamente.');
            $this->redirect(route('admin.pools.index'));
        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }

    public function render()
    {
        $pools = Pool::all();
        $units = Unit::with('building.complex')->get();
        $users = User::whereIn('role', [Role::Propietario, Role::Inquilino])->get();

        return view('livewire.admin.pools.register-entry', [
            'pools' => $pools,
            'units' => $units,
            'users' => $users,
        ])->layout('components.layouts.app', ['title' => 'Registrar Ingreso a Pileta']);
    }
}
