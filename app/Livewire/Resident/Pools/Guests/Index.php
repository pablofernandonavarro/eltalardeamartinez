<?php

namespace App\Livewire\Resident\Pools\Guests;

use App\Models\PoolGuest;
use Livewire\Component;

class Index extends Component
{
    public ?int $unitId = null;

    public function mount(): void
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->pluck('unit_id')->all();
        $this->unitId = $units[0] ?? null;
    }

    public function delete(int $guestId): void
    {
        $user = auth()->user();

        $guest = PoolGuest::query()
            ->where('id', $guestId)
            ->where('created_by_user_id', $user->id)
            ->firstOrFail();

        // Extra: validar que el usuario tenga esa unidad
        $allowedUnitIds = $user->currentUnitUsers()->pluck('unit_id')->all();
        if (! in_array($guest->unit_id, $allowedUnitIds, true)) {
            abort(403);
        }

        $guest->delete();
        session()->flash('message', 'Invitado eliminado.');
    }

    public function render()
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->with(['unit.building.complex'])->get();

        $guests = collect();
        if ($this->unitId) {
            $guests = PoolGuest::query()
                ->where('created_by_user_id', $user->id)
                ->where('unit_id', $this->unitId)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.resident.pools.guests.index', [
            'unitUsers' => $unitUsers,
            'guests' => $guests,
        ])->layout('components.layouts.resident', ['title' => 'Mis invitados']);
    }
}
