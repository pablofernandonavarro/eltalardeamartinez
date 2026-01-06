<?php

namespace App\Livewire\Resident\Pools\Guests;

use App\Models\PoolEntry;
use App\Models\PoolGuest;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UsedGuests extends Component
{
    public ?int $unitId = null;
    public ?string $filterMonth = null;

    public function mount(): void
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->pluck('unit_id')->all();
        $this->unitId = $units[0] ?? null;
        
        // Por defecto, mostrar el mes actual
        $this->filterMonth = now()->format('Y-m');
    }

    public function render()
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->with(['unit.building.complex'])->get();

        $usedGuests = collect();
        
        if ($this->unitId && $this->filterMonth) {
            // Obtener todos los invitados del usuario en esta unidad
            $guestIds = PoolGuest::query()
                ->where('created_by_user_id', $user->id)
                ->where('unit_id', $this->unitId)
                ->pluck('id');

            // Obtener historial de uso de estos invitados
            $startDate = \Carbon\Carbon::parse($this->filterMonth . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $usedGuests = DB::table('pool_entry_guests')
                ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
                ->join('pool_guests', 'pool_guests.id', '=', 'pool_entry_guests.pool_guest_id')
                ->join('pools', 'pools.id', '=', 'pool_entries.pool_id')
                ->leftJoin('users', 'users.id', '=', 'pool_entries.user_id')
                ->leftJoin('residents', 'residents.id', '=', 'pool_entries.resident_id')
                ->whereIn('pool_entry_guests.pool_guest_id', $guestIds)
                ->whereBetween('pool_entries.entered_at', [$startDate, $endDate])
                ->select([
                    'pool_guests.id as guest_id',
                    'pool_guests.name as guest_name',
                    'pool_guests.document_type',
                    'pool_guests.document_number',
                    'pool_guests.profile_photo_path',
                    'pool_entries.entered_at',
                    'pool_entries.exited_at',
                    'pools.name as pool_name',
                    DB::raw('COALESCE(users.name, residents.name) as entered_by_name'),
                ])
                ->orderBy('pool_entries.entered_at', 'desc')
                ->get();
        }

        return view('livewire.resident.pools.guests.used-guests', [
            'unitUsers' => $unitUsers,
            'usedGuests' => $usedGuests,
        ])->layout('components.layouts.resident', ['title' => 'Invitados Utilizados']);
    }
}
