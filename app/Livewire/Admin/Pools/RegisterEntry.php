<?php

namespace App\Livewire\Admin\Pools;

use App\Models\Pool;
use App\Models\PoolSetting;
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
            
            // Validar límite diario
            $enteredAtDate = \Carbon\Carbon::parse($this->enteredAt);
            $isWeekend = $enteredAtDate->isWeekend();
            $maxGuestsToday = $isWeekend
                ? PoolSetting::get('max_guests_weekend', 2)
                : PoolSetting::get('max_guests_weekday', 4);

            if ($this->guestsCount > $maxGuestsToday) {
                $dayType = $isWeekend ? 'fines de semana/feriados' : 'días de semana';
                $this->addError('guestsCount', "Máximo {$maxGuestsToday} invitados permitidos en {$dayType}.");
                return;
            }

            // Validar límite mensual según tipo de día
            $monthStart = $enteredAtDate->copy()->startOfMonth();
            $monthEnd = $enteredAtDate->copy()->endOfMonth();

            if ($isWeekend) {
                // Validar límite mensual de fines de semana
                $usedWeekendsMonth = \App\Models\PoolEntry::forUnit($unit->id)
                    ->where('pool_id', $pool->id)
                    ->whereBetween('entered_at', [$monthStart, $monthEnd])
                    ->whereRaw('DAYOFWEEK(entered_at) IN (1, 7)') // Sábado y Domingo
                    ->sum('guests_count');

                $maxGuestsWeekendMonth = PoolSetting::get('max_guests_weekend_month', 3);
                $availableWeekendMonth = max(0, $maxGuestsWeekendMonth - $usedWeekendsMonth);

                if ($this->guestsCount > $availableWeekendMonth) {
                    $this->addError('guestsCount', "Límite mensual de fines de semana excedido: {$usedWeekendsMonth}/{$maxGuestsWeekendMonth} invitados usados. Disponible: {$availableWeekendMonth}.");
                    return;
                }
            } else {
                // Validar límite mensual de días de semana
                $usedWeekdaysMonth = \App\Models\PoolEntry::forUnit($unit->id)
                    ->where('pool_id', $pool->id)
                    ->whereBetween('entered_at', [$monthStart, $monthEnd])
                    ->whereRaw('DAYOFWEEK(entered_at) NOT IN (1, 7)') // Lunes a Viernes
                    ->sum('guests_count');

                $maxGuestsMonth = PoolSetting::get('max_guests_month', 5);
                $availableMonth = max(0, $maxGuestsMonth - $usedWeekdaysMonth);

                if ($this->guestsCount > $availableMonth) {
                    $this->addError('guestsCount', "Límite mensual de días de semana excedido: {$usedWeekdaysMonth}/{$maxGuestsMonth} invitados usados. Disponible: {$availableMonth}.");
                    return;
                }
            }

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
