<?php

namespace App\Livewire\Resident\Pools;

use App\Models\PoolDayPass;
use App\Models\PoolSetting;
use App\Models\Resident;
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

    public ?Resident $currentResident = null;

    public function mount(): void
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->pluck('unit_id')->all();

        $this->unitId = $units[0] ?? null;

        $this->loadOrCreatePass();
        $this->loadCurrentResident();
    }

    public function updatedUnitId(): void
    {
        $this->loadOrCreatePass();
        $this->loadCurrentResident();
        
        // SANITIZACIÓN AL CAMBIAR UNIDAD: Re-validar límites
        $limits = $this->calculateAvailableLimits();
        if ($limits['has_limits'] && $limits['max_guests_today'] !== null) {
            $maxAllowed = $limits['max_guests_today'];
            if (count($this->selectedGuestIds) > $maxAllowed) {
                $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, $maxAllowed);
            }
        }
    }

    public function updatedSelectedGuestIds(): void
    {
        // Normalizar: Livewire puede mandar strings y/o duplicados
        $this->selectedGuestIds = array_values(array_unique(array_map('intval', $this->selectedGuestIds)));
        
        // SANITIZACIÓN AUTOMÁTICA: Forzar el límite del reglamento
        $limits = $this->calculateAvailableLimits();
        if ($limits['has_limits'] && $limits['max_guests_today'] !== null) {
            $maxAllowed = $limits['max_guests_today'];
            if (count($this->selectedGuestIds) > $maxAllowed) {
                // TRUNCAR AUTOMÁTICAMENTE: No se puede exceder el límite
                $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, $maxAllowed);
            }
        }
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

    protected function loadCurrentResident(): void
    {
        $user = auth()->user();

        if (! $this->unitId) {
            $this->currentResident = null;
            $this->dispatch('resident-personal-qr-updated', token: null);

            return;
        }

        // Buscar el residente que tenga cuenta de autenticación con este usuario
        // El auth_user_id indica que este residente tiene su propia cuenta
        $resident = Resident::query()
            ->where('unit_id', $this->unitId)
            ->where('auth_user_id', $user->id)
            ->active()
            ->first();

        $this->currentResident = $resident;

        // Si el residente existe, es mayor de 18 y no tiene QR, generarlo
        if ($resident && $resident->canHavePersonalQr() && ! $resident->qr_token) {
            $resident->generateQrToken();
            $resident->refresh();
            $this->currentResident = $resident;
        }

        $this->dispatch('resident-personal-qr-updated', token: $resident?->qr_token);
    }

    public function regeneratePersonalQr(): void
    {
        if (! $this->currentResident || ! $this->currentResident->canHavePersonalQr()) {
            $this->addError('error', 'No se puede regenerar el QR personal.');

            return;
        }

        $this->currentResident->qr_token = (string) Str::uuid();
        $this->currentResident->save();
        $this->currentResident->refresh();

        $this->dispatch('resident-personal-qr-updated', token: $this->currentResident->qr_token);

        session()->flash('message', 'QR personal regenerado.');
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

        // VALIDACIÓN: Forzar cumplimiento absoluto del reglamento mensual
        $limitsInfo = $this->calculateAvailableLimits();
        $isWeekend = $limitsInfo['is_weekend'] ?? false;
        
        // Obtener el límite mensual disponible según tipo de día
        $availableMonth = $isWeekend 
            ? ($limitsInfo['available_weekend_month'] ?? 999)
            : ($limitsInfo['available_weekday_month'] ?? 999);
        
        $usedThisMonth = $isWeekend
            ? ($limitsInfo['used_weekends_month'] ?? 0)
            : ($limitsInfo['used_weekdays_month'] ?? 0);
            
        $maxMonthly = $isWeekend
            ? ($limitsInfo['max_guests_weekend_month'] ?? 2)
            : ($limitsInfo['max_guests_weekday_month'] ?? 4);
        
        // Validar límite mensual
        if (count($this->selectedGuestIds) > $availableMonth) {
            $dayType = $isWeekend ? 'fines de semana' : 'días de semana';
            $this->addError('error', "LÍMITE MENSUAL EXCEDIDO: Has usado {$usedThisMonth} de {$maxMonthly} invitados únicos en {$dayType} este mes. Solo podés agregar {$availableMonth} invitados más.");
            
            // Truncar al disponible mensual
            $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, max(0, (int)$availableMonth));
        }

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

        // Calcular límites disponibles para hoy
        $limitsInfo = $this->calculateAvailableLimits();

        return view('livewire.resident.pools.day-pass', [
            'units' => $units,
            'guests' => $guests,
            'pass' => $this->pass,
            'selectedGuestsCount' => $selectedGuestsCount,
            'limitsInfo' => $limitsInfo,
        ])->layout('components.layouts.resident', ['title' => 'Mi QR de Pileta (hoy)']);
    }

    protected function calculateAvailableLimits(): array
    {
        if (! $this->unitId) {
            return [
                'has_limits' => false,
                'is_weekend' => false,
                'max_guests_today' => null,
                'max_guests_month' => null,
                'used_this_month' => 0,
                'available_month' => null,
                'message' => null,
            ];
        }

        $unit = \App\Models\Unit::find($this->unitId);
        if (! $unit) {
            return ['has_limits' => false];
        }

        $today = now();
        $isWeekend = $today->isWeekend();
        $dayOfWeek = $today->dayOfWeek;

        // Contar invitados únicos por tipo de día este mes (TODOS LOS POOLS - el límite es por unidad)
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        // Contar invitados únicos usados en DÍAS DE SEMANA este mes (TODOS LOS POOLS)
        $usedWeekdaysMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)') // Lunes=2 a Viernes=6
            ->selectRaw('COUNT(DISTINCT pool_entry_guests.pool_guest_id) as total')
            ->value('total');
        
        // Contar invitados únicos usados en FINES DE SEMANA este mes (TODOS LOS POOLS)
        $usedWeekendsMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) IN (1, 7)') // 1=Domingo, 7=Sábado
            ->selectRaw('COUNT(DISTINCT pool_entry_guests.pool_guest_id) as total')
            ->value('total');

        // Obtener límites diarios según tipo de día
        $maxGuestsWeekdayDay = PoolSetting::get('max_guests_weekday', 4);
        $maxGuestsWeekendDay = PoolSetting::get('max_guests_weekend', 2);

        // Obtener límites mensuales para ambos tipos de día
        $maxGuestsWeekdayMonth = PoolSetting::get('max_guests_month', 5); // General mensual
        $maxGuestsWeekendMonth = PoolSetting::get('max_guests_weekend_month', 3); // Específico fines de semana
        $availableWeekdayMonth = max(0, $maxGuestsWeekdayMonth - $usedWeekdaysMonth);
        $availableWeekendMonth = max(0, $maxGuestsWeekendMonth - $usedWeekendsMonth);

        // Obtener límite diario según tipo de día
        $maxGuestsToday = $isWeekend ? $maxGuestsWeekendDay : $maxGuestsWeekdayDay;

        $hasQuota = $isWeekend ? ($availableWeekendMonth > 0) : ($availableWeekdayMonth > 0);

        return [
            'has_limits' => true,
            'is_weekend' => $isWeekend,
            'max_guests_today' => $maxGuestsToday,
            // Límites de días de semana
            'max_guests_weekday_month' => $maxGuestsWeekdayMonth,
            'used_weekdays_month' => $usedWeekdaysMonth,
            'available_weekday_month' => $availableWeekdayMonth,
            // Límites de fines de semana
            'max_guests_weekend_month' => $maxGuestsWeekendMonth,
            'used_weekends_month' => $usedWeekendsMonth,
            'available_weekend_month' => $availableWeekendMonth,
            // Estado general
            'has_quota' => $hasQuota,
            'message' => $hasQuota 
                ? ($isWeekend 
                    ? "Fin de semana: {$availableWeekendMonth} de {$maxGuestsWeekendMonth} invitados únicos disponibles. Los invitados pueden reingresar el mismo día." 
                    : "Día de semana: {$availableWeekdayMonth} de {$maxGuestsWeekdayMonth} invitados únicos disponibles. Los invitados pueden reingresar el mismo día.")
                : ($isWeekend
                    ? "Límite de fin de semana agotado: Has usado {$usedWeekendsMonth} de {$maxGuestsWeekendMonth} invitados únicos en fines de semana este mes."
                    : "Límite de día de semana agotado: Has usado {$usedWeekdaysMonth} de {$maxGuestsWeekdayMonth} invitados únicos en días de semana este mes."),
        ];
    }
}
