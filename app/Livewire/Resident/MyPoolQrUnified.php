<?php

namespace App\Livewire\Resident;

use App\Models\PoolDayPass;
use App\Models\PoolSetting;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class MyPoolQrUnified extends Component
{
    // QR Personal
    public ?Resident $resident = null;
    public ?User $user = null;
    public bool $useUserQr = false;

    // Day Pass (invitados)
    public ?int $unitId = null;
    public array $selectedGuestIds = [];
    public ?PoolDayPass $pass = null;

    public function mount(): void
    {
        $this->user = auth()->user();
        $units = $this->user->currentUnitUsers()->pluck('unit_id')->all();
        $this->unitId = $units[0] ?? null;

        // Buscar el residente asociado a este usuario autenticado
        $this->resident = Resident::query()
            ->where('auth_user_id', $this->user->id)
            ->active()
            ->first();

        // Si no es residente, usar QR del usuario directamente
        if (!$this->resident) {
            $this->useUserQr = true;
            
            // Generar QR para el usuario si no lo tiene
            if (!$this->user->qr_token) {
                $this->user->qr_token = (string) Str::uuid();
                $this->user->save();
            }
        } else {
            // Si el residente existe y puede tener QR, generarlo si no lo tiene
            if ($this->resident->canHavePersonalQr() && ! $this->resident->qr_token) {
                $this->resident->generateQrToken();
                $this->resident->refresh();
            }
        }

        // Cargar day pass para invitados
        $this->loadOrCreatePass();
    }

    public function updatedUnitId(): void
    {
        $this->loadOrCreatePass();
        
        // Re-validar límites al cambiar unidad
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
        // Normalizar
        $this->selectedGuestIds = array_values(array_unique(array_map('intval', $this->selectedGuestIds)));
        
        // Forzar límite del reglamento
        $limits = $this->calculateAvailableLimits();
        if ($limits['has_limits'] && $limits['max_guests_today'] !== null) {
            $maxAllowed = $limits['max_guests_today'];
            if (count($this->selectedGuestIds) > $maxAllowed) {
                $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, $maxAllowed);
            }
        }
    }

    protected function loadOrCreatePass(): void
    {
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
            ->where('user_id', $this->user->id)
            ->first();

        if (! $pass) {
            $pass = PoolDayPass::create([
                'token' => (string) Str::uuid(),
                'date' => $today,
                'unit_id' => $this->unitId,
                'user_id' => $this->user->id,
                'resident_id' => null,
                'guests_allowed' => 0,
            ]);
        }

        $this->pass = $pass;
        $this->selectedGuestIds = $pass->guests()->pluck('pool_guests.id')->map(fn ($id) => (int) $id)->all();
        $this->dispatch('resident-daypass-qr-updated', token: $pass->token);
    }

    public function regenerateQr(): void
    {
        if ($this->useUserQr) {
            $this->user->qr_token = (string) Str::uuid();
            $this->user->save();
            $this->dispatch('resident-qr-updated', token: $this->user->qr_token);
        } else {
            if (! $this->resident || ! $this->resident->canHavePersonalQr()) {
                $this->addError('error', 'No se puede regenerar el QR.');
                return;
            }

            $this->resident->qr_token = (string) Str::uuid();
            $this->resident->save();
            $this->resident->refresh();
            $this->dispatch('resident-qr-updated', token: $this->resident->qr_token);
        }

        session()->flash('message', 'QR personal regenerado exitosamente.');
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

        // Validar invitados pertenecen a esta unidad
        $allowedGuests = \App\Models\PoolGuest::query()
            ->where('created_by_user_id', $this->user->id)
            ->where('unit_id', $this->unitId)
            ->whereIn('id', $this->selectedGuestIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->selectedGuestIds = array_values(array_unique($allowedGuests));

        // Validar límites
        $limitsInfo = $this->calculateAvailableLimits();
        $maxAllowed = $limitsInfo['max_guests_today'] ?? 999;
        $availableMonth = $limitsInfo[$limitsInfo['is_weekend'] ? 'available_weekend_month' : 'available_weekday_month'] ?? 999;
        
        if (count($this->selectedGuestIds) > $maxAllowed) {
            $dayType = $limitsInfo['is_weekend'] ? 'fines de semana/feriados' : 'días de semana';
            $this->addError('error', "Máximo {$maxAllowed} invitados permitidos en {$dayType}.");
            $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, $maxAllowed);
        }
        
        if (count($this->selectedGuestIds) > $availableMonth) {
            $this->addError('error', "Solo quedan {$availableMonth} invitados disponibles este mes.");
            $this->selectedGuestIds = array_slice($this->selectedGuestIds, 0, (int)$availableMonth);
        }

        $this->pass->guests()->sync($this->selectedGuestIds);
        $this->pass->update(['guests_allowed' => count($this->selectedGuestIds)]);

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

        $this->pass->update(['token' => (string) Str::uuid()]);
        $this->pass->refresh();
        $this->dispatch('resident-daypass-qr-updated', token: $this->pass->token);
        session()->flash('message', 'QR diario regenerado.');
        $this->loadOrCreatePass();
    }

    public function render()
    {
        $qrToken = $this->useUserQr ? $this->user->qr_token : $this->resident?->qr_token;
        
        $units = $this->user->currentUnitUsers()->with('unit.building.complex')->get();
        
        $guests = collect();
        if ($this->unitId) {
            $guests = \App\Models\PoolGuest::query()
                ->where('created_by_user_id', $this->user->id)
                ->where('unit_id', $this->unitId)
                ->orderBy('name')
                ->get();
        }

        $selectedGuestsCount = count($this->selectedGuestIds);
        $limitsInfo = $this->calculateAvailableLimits();
        
        return view('livewire.resident.my-pool-qr-unified', [
            'resident' => $this->resident,
            'user' => $this->user,
            'useUserQr' => $this->useUserQr,
            'qrToken' => $qrToken,
            'units' => $units,
            'guests' => $guests,
            'pass' => $this->pass,
            'selectedGuestsCount' => $selectedGuestsCount,
            'limitsInfo' => $limitsInfo,
        ])->layout('components.layouts.resident', ['title' => 'Mi QR de Pileta']);
    }

    protected function calculateAvailableLimits(): array
    {
        if (! $this->unitId) {
            return [
                'has_limits' => false,
                'is_weekend' => false,
                'max_guests_today' => null,
            ];
        }

        $unit = \App\Models\Unit::find($this->unitId);
        if (! $unit) {
            return ['has_limits' => false];
        }

        $today = now();
        $isWeekend = $today->isWeekend();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        $usedWeekdaysMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
            ->selectRaw('COUNT(DISTINCT pool_entry_guests.pool_guest_id) as total')
            ->value('total');
        
        $usedWeekendsMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) IN (1, 7)')
            ->selectRaw('COUNT(DISTINCT pool_entry_guests.pool_guest_id) as total')
            ->value('total');

        $maxGuestsWeekdayMonth = PoolSetting::get('max_guests_weekday', 4);
        $maxGuestsWeekendMonth = PoolSetting::get('max_guests_weekend', 2);
        $availableWeekdayMonth = max(0, $maxGuestsWeekdayMonth - $usedWeekdaysMonth);
        $availableWeekendMonth = max(0, $maxGuestsWeekendMonth - $usedWeekendsMonth);
        
        $maxGuestsToday = $isWeekend 
            ? PoolSetting::get('max_guests_weekend_day', 2)
            : PoolSetting::get('max_guests_weekday_day', 4);

        $hasQuota = $isWeekend ? ($availableWeekendMonth > 0) : ($availableWeekdayMonth > 0);

        return [
            'has_limits' => true,
            'is_weekend' => $isWeekend,
            'max_guests_today' => $maxGuestsToday,
            'max_guests_weekday_month' => $maxGuestsWeekdayMonth,
            'used_weekdays_month' => $usedWeekdaysMonth,
            'available_weekday_month' => $availableWeekdayMonth,
            'max_guests_weekend_month' => $maxGuestsWeekendMonth,
            'used_weekends_month' => $usedWeekendsMonth,
            'available_weekend_month' => $availableWeekendMonth,
            'has_quota' => $hasQuota,
        ];
    }
}
