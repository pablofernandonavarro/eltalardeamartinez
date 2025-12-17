<?php

namespace App\Livewire\Resident\Pools;

use App\Models\PoolDayPass;
use Illuminate\Support\Str;
use Livewire\Component;

class DayPass extends Component
{
    public ?int $unitId = null;

    /**
     * Invitados seleccionados para HOY (IDs de pool_guests)
     *
     * @var array<int>
     */
    public array $selectedGuestIds = [];

    public ?PoolDayPass $pass = null;

    public function mount(): void
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->pluck('unit_id')->all();

        $this->unitId = $units[0] ?? null;

        $this->loadOrCreatePass();
    }

    public function updatedUnitId(): void
    {
        $this->loadOrCreatePass();
    }

    public function updatedSelectedGuestIds(): void
    {
        // Normalizar: Livewire puede mandar strings y/o duplicados
        $this->selectedGuestIds = array_values(array_unique(array_map('intval', $this->selectedGuestIds)));
    }


    protected function loadOrCreatePass(): void
    {
        $user = auth()->user();

        if (! $this->unitId) {
            $this->pass = null;
            $this->selectedGuestIds = [];
            $this->dispatch('resident-daypass-qr-updated', token: null);

            return;
        }

        $today = now()->toDateString();

        $pass = PoolDayPass::query()
            ->whereDate('date', $today)
            ->where('unit_id', $this->unitId)
            ->where('user_id', $user->id)
            ->first();

        if (! $pass) {
            $pass = PoolDayPass::create([
                'token' => (string) Str::uuid(),
                'date' => $today,
                'unit_id' => $this->unitId,
                'user_id' => $user->id,
                'resident_id' => null,
                'guests_allowed' => 0,
            ]);
        }

        $this->pass = $pass;

        // Cargar invitados ya asociados al pase
        $this->selectedGuestIds = $pass->guests()->pluck('pool_guests.id')->map(fn ($id) => (int) $id)->all();

        $this->dispatch('resident-daypass-qr-updated', token: $pass->token);
    }

    protected function hasOpenEntryToday(): bool
    {
        if (! $this->pass) {
            return false;
        }

        $q = \App\Models\PoolEntry::query()
            ->where('unit_id', $this->pass->unit_id)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at');

        if ($this->pass->resident_id) {
            $q->where('resident_id', $this->pass->resident_id);
        } else {
            $q->where('user_id', $this->pass->user_id);
        }

        return $q->exists();
    }

    public function save(): void
    {
        $this->validate([
            'unitId' => 'required|exists:units,id',
            'selectedGuestIds' => 'array',
            'selectedGuestIds.*' => 'integer',
        ], [
            'unitId.required' => 'Debe seleccionar una unidad.',
        ]);

        if (! $this->pass) {
            $this->loadOrCreatePass();
        }

        // Si está adentro, no permitimos cambiar el pase (para que el control sea consistente)
        if ($this->hasOpenEntryToday()) {
            $this->addError('error', 'No podés modificar los invitados mientras hay un ingreso abierto. Registrá la salida y volvé a intentarlo.');

            return;
        }

        // Validar que los invitados seleccionados pertenezcan a esta unidad y sean del usuario
        $user = auth()->user();
        $allowedGuests = \App\Models\PoolGuest::query()
            ->where('created_by_user_id', $user->id)
            ->where('unit_id', $this->unitId)
            ->whereIn('id', $this->selectedGuestIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Si alguno no corresponde, lo descartamos (y volvemos a sincronizar limpio)
        $this->selectedGuestIds = array_values(array_unique($allowedGuests));

        $this->pass->guests()->sync($this->selectedGuestIds);
        $this->pass->update([
            'guests_allowed' => count($this->selectedGuestIds),
        ]);

        session()->flash('message', 'Invitados del día guardados.');

        $this->loadOrCreatePass();
    }

    public function regenerateToken(): void
    {
        if (! $this->pass) {
            return;
        }

        if ($this->pass->isUsed()) {
            $this->addError('error', 'No se puede regenerar: el pase ya fue utilizado.');

            return;
        }

        $this->pass->update([
            'token' => (string) Str::uuid(),
        ]);

        $this->pass->refresh();

        // Disparar inmediatamente para que el QR se actualice sin depender del reload
        $this->dispatch('resident-daypass-qr-updated', token: $this->pass->token);

        session()->flash('message', 'QR regenerado.');

        $this->loadOrCreatePass();
    }

    public function render()
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->with('unit.building.complex')->get();

        $guests = collect();
        if ($this->unitId) {
            $guests = \App\Models\PoolGuest::query()
                ->where('created_by_user_id', $user->id)
                ->where('unit_id', $this->unitId)
                ->orderBy('name')
                ->get();
        }

        $selectedGuestsCount = count(array_values(array_unique(array_map('intval', $this->selectedGuestIds))));

        return view('livewire.resident.pools.day-pass', [
            'units' => $units,
            'guests' => $guests,
            'pass' => $this->pass,
            'selectedGuestsCount' => $selectedGuestsCount,
        ])->layout('components.layouts.resident', ['title' => 'Mi QR de Pileta (hoy)']);
    }
}
