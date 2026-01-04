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

        // Buscar el residente asociado al usuario en esta unidad
        $resident = Resident::query()
            ->where('unit_id', $this->unitId)
            ->where('user_id', $user->id)
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

        // VALIDACIÓN TRIPLE: Forzar cumplimiento absoluto del reglamento
        $limitsInfo = $this->calculateAvailableLimits();
        $maxAllowed = $limitsInfo['max_guests_today'] ?? 999;
        $maxMonthly = $limitsInfo['max_guests_month'] ?? 999;
        $usedThisMonth = $limitsInfo['used_this_month'] ?? 0;
        $availableMonth = $limitsInfo['available_month'] ?? 999;
        
        // Validar límite diario
        if (count($this->selectedGuestIds) > $maxAllowed) {
            $isWeekend = $limitsInfo['is_weekend'] ?? false;
            $dayType = $isWeekend ? 'fines de semana/feriados' : 'días de semana';
            
            $this->addError('error', "REGLAMENTO VIOLADO: Máximo {$maxAllowed} invitados permitidos en {$dayType}. El sistema ha ajustado automáticamente la cantidad.");
            
            // Truncar automáticamente
            $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, $maxAllowed);
        }
        
        // Validar límite mensual
        if (count($this->selectedGuestIds) > $availableMonth) {
            $this->addError('error', "LÍMITE MENSUAL EXCEDIDO: Has usado {$usedThisMonth} de {$maxMonthly} invitados este mes. Solo puedes agregar {$availableMonth} invitados más.");
            
            // Truncar al disponible mensual
            $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, (int)$availableMonth);
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

        // Obtener pool habilitado (asumimos el primero)
        $pool = \App\Models\Pool::query()->first();
        if (! $pool) {
            return ['has_limits' => false];
        }

        // Calcular invitados usados este mes
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $usedThisMonth = \App\Models\PoolEntry::forUnit($unit->id)
            ->where('pool_id', $pool->id)
            ->whereBetween('entered_at', [$monthStart, $monthEnd])
            ->sum('guests_count');

        // ⚠️ LÍMITES CONFIGURABLES DINÁMICAMENTE
        $allowExtraPayment = PoolSetting::get('allow_extra_payment', false);
        $maxGuestsMonth = PoolSetting::get('max_guests_month', 5);
        $availableMonth = max(0, $maxGuestsMonth - $usedThisMonth);
        
        if ($isWeekend) {
            // FINES DE SEMANA Y FERIADOS: Leer de configuración
            $maxGuestsToday = PoolSetting::get('max_guests_weekend', 2);

            $paymentMessage = $allowExtraPayment 
                ? 'Pods pagar por invitados extra si exceds el límite.' 
                : 'No se aceptan pagos por invitados extra.';

            return [
                'has_limits' => true,
                'is_weekend' => true,
                'max_guests_today' => $maxGuestsToday,
                'max_guests_month' => $maxGuestsMonth,
                'used_this_month' => $usedThisMonth,
                'available_month' => $availableMonth,
                'allow_extra_payment' => $allowExtraPayment,
                'message' => "Reglamento: Máximo {$maxGuestsToday} invitados los fines de semana y feriados. Límite mensual: {$maxGuestsMonth}. {$paymentMessage}",
            ];
        } else {
            // LUNES A VIERNES: Leer de configuración
            $maxGuestsToday = PoolSetting::get('max_guests_weekday', 4);

            $paymentMessage = $allowExtraPayment 
                ? 'Pods pagar por invitados extra si exceds el límite.' 
                : 'No se aceptan pagos por invitados extra.';

            return [
                'has_limits' => true,
                'is_weekend' => false,
                'max_guests_today' => $maxGuestsToday,
                'max_guests_month' => $maxGuestsMonth,
                'used_this_month' => $usedThisMonth,
                'available_month' => $availableMonth,
                'allow_extra_payment' => $allowExtraPayment,
                'message' => "Reglamento: Máximo {$maxGuestsToday} invitados de lunes a viernes. Límite mensual: {$maxGuestsMonth}. {$paymentMessage}",
            ];
        }
    }
}
