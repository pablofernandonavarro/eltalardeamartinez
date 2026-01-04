<?php

namespace App\Livewire\Banero\Pools;

use App\Models\Pool;
use App\Models\PoolDayPass;
use App\Models\PoolSetting;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Services\PoolAccessService;
use Livewire\Component;

class Scanner extends Component
{
    public ?\App\Models\PoolShift $activeShift = null;

    /**
     * action: entry | exit (se decide automáticamente al escanear)
     */
    public string $action = 'entry';

    public string $token = '';

    public ?PoolDayPass $pass = null;

    public ?Resident $scannedResident = null;

    public ?int $poolId = null;

    public ?string $exitNotes = null;

    /**
     * IDs de invitados (pool_guests) que efectivamente ingresan.
     *
     * @var array<int>
     */
    public array $selectedGuestIds = [];

    public bool $showGuestList = false;

    public function mount(): void
    {
        $this->activeShift = \App\Models\PoolShift::getActiveShiftForUser(auth()->id());

        if (! $this->activeShift) {
            session()->flash('error', 'Debes iniciar tu turno antes de poder escanear QRs.');
            $this->redirect(route('banero.my-shift'), navigate: true);
        }

        // Asignar automáticamente la pileta del turno activo
        $this->poolId = $this->activeShift->pool_id;
    }

    public function updatedToken(): void
    {
        // Autocargar cuando el scanner setea el token
        if (strlen(trim($this->token)) >= 10) {
            $this->loadPass();
        }
    }

    public function resetScanner(): void
    {
        $this->resetErrorBag();

        $this->token = '';
        $this->pass = null;
        $this->scannedResident = null;
        $this->poolId = null;
        $this->exitNotes = null;
        $this->selectedGuestIds = [];
        $this->showGuestList = false;
        $this->action = 'entry';

        // Re-habilitar cámara en el frontend
        $this->dispatch('banero-scanner-reset');
    }

    public function toggleGuestList(): void
    {
        $this->showGuestList = ! $this->showGuestList;
    }

    public function selectAllGuests(): void
    {
        if (! $this->pass) {
            return;
        }

        // Aplicar límite del reglamento
        $maxAllowed = $this->calculateMaxGuestsAllowedToday();
        $allGuestIds = $this->pass->guests->pluck('id')->map(fn ($id) => (int) $id)->all();
        
        $this->selectedGuestIds = array_slice($allGuestIds, 0, $maxAllowed);
    }

    public function clearGuests(): void
    {
        $this->selectedGuestIds = [];
    }

    public function loadPass(): void
    {
        $this->resetErrorBag();
        $this->pass = null;
        $this->scannedResident = null;
        $this->poolId = null;
        $this->exitNotes = null;
        $this->selectedGuestIds = [];
        $this->showGuestList = false;
        $this->action = 'entry';

        $token = trim($this->token);
        if ($token === '') {
            $this->addError('token', 'Debe ingresar o escanear un token.');

            return;
        }

        // Intentar buscar primero como QR personal de residente
        $resident = Resident::query()
            ->with(['unit.building.complex', 'user', 'authUser'])
            ->where('qr_token', $token)
            ->active()
            ->first();

        if ($resident) {
            // Es un QR personal de residente
            if (! $resident->canHavePersonalQr()) {
                $this->addError('token', 'Este residente no tiene autorización para usar QR personal.');

                return;
            }

            $this->scannedResident = $resident;

            // Acción automática según estado actual
            $openEntry = $this->findOpenEntryForResident($resident);
            $this->action = $openEntry ? 'exit' : 'entry';

            return;
        }

        // Si no es un QR de residente, buscar como day-pass
        $pass = PoolDayPass::query()
            ->with(['unit.building.complex', 'user', 'resident', 'guests', 'poolEntry.pool', 'poolEntry.guests'])
            ->where('token', $token)
            ->first();

        if (! $pass) {
            $this->addError('token', 'Token inválido.');

            return;
        }

        if ($pass->date->toDateString() !== now()->toDateString()) {
            $this->addError('token', 'El pase no corresponde a hoy.');

            return;
        }

        $this->pass = $pass;

        // Acción automática según estado actual
        $openEntry = $this->findOpenEntryForPass();
        $this->action = $openEntry ? 'exit' : 'entry';

        // Por defecto, seleccionar todos los invitados precargados (respetando límite del reglamento)
        $maxAllowed = $this->calculateMaxGuestsAllowedToday();
        $allGuestIds = $pass->guests->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selectedGuestIds = array_slice($allGuestIds, 0, $maxAllowed);
    }

    public function confirm(PoolAccessService $poolAccessService): void
    {
        if (! $this->pass && ! $this->scannedResident) {
            $this->addError('error', 'Primero escanee un QR.');

            return;
        }

        if ($this->action !== 'entry') {
            $this->addError('error', 'Este QR indica que hay un ingreso abierto. Registre la salida.');

            return;
        }

        // Manejar QR de residente
        if ($this->scannedResident) {
            $this->confirmResidentEntry($poolAccessService);

            return;
        }

        // Evitar doble entrada por carrera
        $openEntry = $this->findOpenEntryForPass();
        if ($openEntry) {
            $this->addError('error', 'Este usuario ya está en la pileta. Registre la salida antes de volver a ingresar.');

            return;
        }

        $this->validate([
            'poolId' => 'required|exists:pools,id',
            'selectedGuestIds' => 'array',
            'selectedGuestIds.*' => 'integer',
        ], [
            'poolId.required' => 'Debe seleccionar una pileta.',
        ]);

        // Validar que los invitados seleccionados sean parte del pase
        $allowedIds = $this->pass->guests->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selected = array_values(array_unique(array_map('intval', $this->selectedGuestIds)));
        $selected = array_values(array_intersect($selected, $allowedIds));
        $this->selectedGuestIds = $selected;

        $guestsCount = count($this->selectedGuestIds);

        // VALIDACIÓN 1: No más que los precargados
        if ($guestsCount > $this->pass->guests_allowed) {
            $this->addError('selectedGuestIds', 'No puede registrar más invitados que los precargados por el usuario.');

            return;
        }

        // VALIDACIÓN 2: Cumplir con el reglamento (límite diario absoluto)
        $maxAllowedByRegulation = $this->calculateMaxGuestsAllowedToday();
        if ($guestsCount > $maxAllowedByRegulation) {
            $isWeekend = now()->isWeekend();
            $dayType = $isWeekend ? 'fines de semana/feriados' : 'días de semana';
            
            $this->addError('selectedGuestIds', "REGLAMENTO VIOLADO: Máximo {$maxAllowedByRegulation} invitados permitidos en {$dayType}. No se aceptan pagos por invitados extra.");

            return;
        }
        
        // VALIDACIÓN 3: Cumplir con el límite mensual
        $unit = Unit::findOrFail($this->pass->unit_id);
        $pool = Pool::findOrFail($this->poolId);
        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        $usedThisMonth = \App\Models\PoolEntry::forUnit($unit->id)
            ->where('pool_id', $pool->id)
            ->whereBetween('entered_at', [$monthStart, $monthEnd])
            ->sum('guests_count');
        
        $maxGuestsMonth = PoolSetting::get('max_guests_month', 5);
        $availableMonth = max(0, $maxGuestsMonth - $usedThisMonth);
        
        if ($guestsCount > $availableMonth) {
            $this->addError('selectedGuestIds', "LÍMITE MENSUAL EXCEDIDO: Has usado {$usedThisMonth} de {$maxGuestsMonth} invitados este mes. Solo puedes agregar {$availableMonth} invitados más.");
            
            return;
        }

        try {
            /** @var Pool $pool */
            $pool = Pool::findOrFail($this->poolId);

            /** @var Unit $unit */
            $unit = Unit::findOrFail($this->pass->unit_id);

            $entry = null;

            if ($this->pass->resident_id) {
                /** @var Resident $resident */
                $resident = Resident::findOrFail($this->pass->resident_id);
                $entry = $poolAccessService->registerResidentEntry($pool, $unit, $resident, $guestsCount, now()->toDateTimeString());
            } else {
                /** @var User $user */
                $user = User::findOrFail($this->pass->user_id);
                $entry = $poolAccessService->registerEntry($pool, $unit, $user, $guestsCount, now()->toDateTimeString());
            }

            if ($guestsCount > 0) {
                $entry->guests()->sync($this->selectedGuestIds);
            }

            $this->pass->update([
                'used_at' => now(),
                'used_by_user_id' => auth()->id(),
                'used_pool_id' => $pool->id,
                'used_guests_count' => $guestsCount,
                'pool_entry_id' => $entry->id,
            ]);

            session()->flash('message', 'Ingreso registrado correctamente. Para salir, vuelva a escanear el QR.');

            // Actualizar estado a 'exit' porque ahora está adentro
            $this->action = 'exit';
            $this->poolId = null;
            $this->selectedGuestIds = [];
            // Mantenemos pass y token para facilitar la salida
        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }

    protected function findOpenEntryForPass(): ?\App\Models\PoolEntry
    {
        if (! $this->pass) {
            return null;
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

        return $q->latest('entered_at')->first();
    }

    protected function findOpenEntryForResident(Resident $resident): ?\App\Models\PoolEntry
    {
        return \App\Models\PoolEntry::query()
            ->where('unit_id', $resident->unit_id)
            ->where('resident_id', $resident->id)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();
    }

    protected function confirmResidentEntry(PoolAccessService $poolAccessService): void
    {
        if (! $this->scannedResident) {
            return;
        }

        // Evitar doble entrada
        $openEntry = $this->findOpenEntryForResident($this->scannedResident);
        if ($openEntry) {
            $this->addError('error', 'Este residente ya está en la pileta. Registre la salida antes de volver a ingresar.');

            return;
        }

        $this->validate([
            'poolId' => 'required|exists:pools,id',
        ], [
            'poolId.required' => 'Debe seleccionar una pileta.',
        ]);

        try {
            /** @var Pool $pool */
            $pool = Pool::findOrFail($this->poolId);

            /** @var Unit $unit */
            $unit = Unit::findOrFail($this->scannedResident->unit_id);

            // Registrar entrada del residente sin invitados
            $poolAccessService->registerResidentEntry($pool, $unit, $this->scannedResident, 0, now()->toDateTimeString());

            session()->flash('message', 'Ingreso registrado correctamente. Para salir, vuelva a escanear el QR.');

            // Actualizar estado a 'exit' porque ahora está adentro
            $this->action = 'exit';
            $this->poolId = null;
            // Mantenemos scannedResident y token para facilitar la salida
        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }

    public function checkout(): void
    {
        if (! $this->pass && ! $this->scannedResident) {
            $this->addError('error', 'Primero escanee un QR.');

            return;
        }

        if ($this->action !== 'exit') {
            $this->addError('error', 'No hay un ingreso abierto. Registre la entrada.');

            return;
        }

        // Buscar siempre el ingreso abierto (sin salida)
        $entry = $this->scannedResident
            ? $this->findOpenEntryForResident($this->scannedResident)
            : $this->findOpenEntryForPass();

        if (! $entry) {
            $this->addError('error', 'No se encontró un ingreso abierto para hacer salida.');

            return;
        }

        $entry->update([
            'exited_at' => now(),
            'exited_by_user_id' => auth()->id(),
            'exit_notes' => $this->exitNotes,
        ]);

        session()->flash('message', 'Salida registrada correctamente. Puede registrar un nuevo ingreso sin volver a escanear.');

        // Mantener el QR cargado pero limpiar notas y resetear acción a 'entry'
        $this->exitNotes = null;
        $this->action = 'entry';
        $this->poolId = null;
        // NO limpiamos token, pass ni scannedResident para permitir reingreso inmediato
    }

    /**
     * Calcula el límite máximo de invitados según el reglamento para HOY.
     * Lee la configuración dinámica de la base de datos.
     * 
     * @return int
     */
    protected function calculateMaxGuestsAllowedToday(): int
    {
        $isWeekend = now()->isWeekend();
        
        if ($isWeekend) {
            return PoolSetting::get('max_guests_weekend', 2);
        }
        
        return PoolSetting::get('max_guests_weekday', 4);
    }

    public function render()
    {
        // Solo mostrar la pileta del turno activo
        $pool = $this->activeShift ? Pool::find($this->activeShift->pool_id) : null;

        return view('livewire.banero.pools.scanner', [
            'pool' => $pool,
            'pass' => $this->pass,
        ])->layout('components.layouts.banero', ['title' => 'Escanear QR']);
    }
}
