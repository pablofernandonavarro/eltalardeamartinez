<?php

namespace App\Livewire\Resident\Pools\Guests;

use App\Models\Pool;
use App\Models\PoolEntry;
use App\Models\PoolGuest;
use App\Models\PoolSetting;
use App\Models\Unit;
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
        $limitsInfo = null;
        
        if ($this->unitId && $this->filterMonth) {
            // Calcular límites e información
            $limitsInfo = $this->calculateLimitsInfo($this->unitId, $this->filterMonth);
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
                    // Priorizar resident_id sobre user_id porque resident_id indica quien ingresó físicamente
                    DB::raw('COALESCE(residents.name, users.name) as entered_by_name'),
                ])
                ->orderBy('pool_entries.entered_at', 'desc')
                ->get();
        }

        return view('livewire.resident.pools.guests.used-guests', [
            'unitUsers' => $unitUsers,
            'usedGuests' => $usedGuests,
            'limitsInfo' => $limitsInfo,
        ])->layout('components.layouts.resident', ['title' => 'Invitados Utilizados']);
    }

    protected function calculateLimitsInfo(int $unitId, string $filterMonth): array
    {
        $unit = Unit::find($unitId);
        if (!$unit) {
            return [];
        }

        // Obtener configuración de límites
        $maxGuestsWeekday = PoolSetting::get('max_guests_weekday', 4);
        $maxGuestsWeekend = PoolSetting::get('max_guests_weekend', 2);
        $maxGuestsMonth = PoolSetting::get('max_guests_month', 5);

        // Calcular período
        $startDate = \Carbon\Carbon::parse($filterMonth . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $today = now();

        // Contar invitados únicos usados en el mes seleccionado
        $usedUniqueThisMonth = DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unitId)
            ->whereBetween('pool_entries.entered_at', [$startDate, $endDate])
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');

        // Contar invitados únicos usados en fines de semana del mes
        $usedWeekendsThisMonth = DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unitId)
            ->whereBetween('pool_entries.entered_at', [$startDate, $endDate])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) IN (1, 7)') // 1=Domingo, 7=Sábado
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');

        // Si es el mes actual, calcular disponibilidad hoy
        $todayInfo = null;
        if ($filterMonth === $today->format('Y-m')) {
            $isWeekend = $today->isWeekend();
            $maxToday = $isWeekend ? $maxGuestsWeekend : $maxGuestsWeekday;
            
            $usedToday = DB::table('pool_entry_guests')
                ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
                ->where('pool_entries.unit_id', $unitId)
                ->whereDate('pool_entries.entered_at', $today->toDateString())
                ->distinct('pool_entry_guests.pool_guest_id')
                ->count('pool_entry_guests.pool_guest_id');

            $todayInfo = [
                'is_weekend' => $isWeekend,
                'max_today' => $maxToday,
                'used_today' => $usedToday,
                'available_today' => max(0, $maxToday - $usedToday),
            ];
        }

        return [
            'max_guests_weekday' => $maxGuestsWeekday,
            'max_guests_weekend' => $maxGuestsWeekend,
            'max_guests_month' => $maxGuestsMonth,
            'used_unique_month' => $usedUniqueThisMonth,
            'used_weekends_month' => $usedWeekendsThisMonth,
            'available_month' => max(0, $maxGuestsMonth - $usedUniqueThisMonth),
            'today' => $todayInfo,
        ];
    }
}
