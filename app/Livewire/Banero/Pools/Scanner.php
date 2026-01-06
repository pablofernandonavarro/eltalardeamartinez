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

    public ?int $scannedUserId = null; // Para usuarios con QR personal

    public ?int $selectedResidentId = null; // Residente seleccionado para day-pass

    public ?int $poolId = null;

    public ?string $exitNotes = null;

    /**
     * IDs de invitados (pool_guests) que efectivamente ingresan.
     *
     * @var array<int>
     */
    public array $selectedGuestIds = [];

    public bool $showGuestList = false;

    /**
     * Flag to prevent double loadPass() calls when scanning from JavaScript
     */
    private bool $skipUpdatedToken = false;

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
        // Evitar doble llamada cuando se escanea desde JavaScript
        if ($this->skipUpdatedToken) {
            $this->skipUpdatedToken = false;
            return;
        }
        
        \Log::info('📱 updatedToken disparado', [
            'token_length' => strlen(trim($this->token)),
            'will_load' => strlen(trim($this->token)) >= 10
        ]);
        
        // Autocargar cuando el scanner setea el token manualmente
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
        $this->scannedUserId = null;
        $this->selectedResidentId = null;
        // NO resetear poolId - debe mantenerse con la pileta del turno activo
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

    /**
     * Método especial para cuando se escanea desde JavaScript
     * Evita la doble llamada a loadPass()
     */
    public function loadPassFromScan(string $scannedToken): void
    {
        $this->skipUpdatedToken = true;
        $this->token = $scannedToken;
        $this->loadPass();
    }

    public function loadPass(): void
    {
        \Log::info('🔍 loadPass INICIADO', [
            'token' => substr($this->token, 0, 20) . '...',
            'token_completo' => $this->token,
            'poolId' => $this->poolId
        ]);
        
        $this->resetErrorBag();
        $this->pass = null;
        $this->scannedResident = null;
        $this->scannedUserId = null;
        $this->selectedResidentId = null;
        // NO resetear poolId - debe mantenerse con la pileta del turno activo
        $this->exitNotes = null;
        $this->selectedGuestIds = [];
        $this->showGuestList = false;
        $this->action = 'entry';

        $token = trim($this->token);
        if ($token === '') {
            \Log::warning('Token vacío');
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

        // Si no es QR de residente, buscar como QR personal de usuario
        $user = User::query()
            ->where('qr_token', $token)
            ->whereNotNull('approved_at')
            ->first();

        if ($user) {
            // Es un QR personal de usuario (propietario/inquilino)
            \Log::info('👥 Usuario con QR personal encontrado', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email
            ]);
            
            $unitUser = $user->currentUnitUsers()->first();
            if (!$unitUser) {
                \Log::error('❌ Usuario no tiene unidad activa');
                $this->addError('token', 'El usuario no tiene una unidad activa asignada.');
                return;
            }
            
            // Guardar el ID del usuario para usarlo en confirm
            $this->scannedUserId = $user->id;
            
            // Crear un "residente virtual" con los datos del usuario para mostrar en la UI
            $this->scannedResident = new Resident();
            $this->scannedResident->id = null; // Marcar como "virtual"
            $this->scannedResident->name = $user->name;
            $this->scannedResident->unit_id = $unitUser->unit_id;
            $this->scannedResident->user_id = $user->id;
            $this->scannedResident->auth_user_id = $user->id;
            
            // Cargar la relación unit manualmente
            $this->scannedResident->setRelation('unit', Unit::with(['building.complex'])->find($unitUser->unit_id));
            
            \Log::info('✅ Usuario guardado', [
                'user_id' => $this->scannedUserId,
                'name' => $user->name,
                'unit_id' => $unitUser->unit_id
            ]);
            
            // Acción automática según estado actual
            $openEntry = $this->findOpenEntryForUser($user);
            $this->action = $openEntry ? 'exit' : 'entry';

            return;
        }

        // Si no es un QR de usuario ni residente, buscar como day-pass
        $pass = PoolDayPass::query()
            ->with(['unit.building.complex', 'user', 'resident', 'guests', 'poolEntry.pool', 'poolEntry.guests'])
            ->where('token', $token)
            ->first();

        if (! $pass) {
            $this->addError('token', 'Token inválido.');

            return;
        }

        if ($pass->date->toDateString() !== now()->toDateString()) {
            \Log::warning('⚠️ Fecha incorrecta', ['pass_date' => $pass->date->toDateString(), 'today' => now()->toDateString()]);
            $this->addError('token', 'El pase no corresponde a hoy.');

            return;
        }

        $this->pass = $pass;
        \Log::info('✅ Pass cargado', [
            'pass_id' => $pass->id,
            'unit_id' => $pass->unit_id,
            'guests_count' => $pass->guests->count(),
            'guests_allowed' => $pass->guests_allowed
        ]);

        // Acción automática según estado actual
        $openEntry = $this->findOpenEntryForPass();
        $this->action = $openEntry ? 'exit' : 'entry';
        \Log::info('📍 Acción determinada', ['action' => $this->action, 'openEntry_exists' => (bool)$openEntry]);

        // Por defecto, seleccionar todos los invitados precargados (respetando límite del reglamento)
        $maxAllowed = $this->calculateMaxGuestsAllowedToday();
        $allGuestIds = $pass->guests->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selectedGuestIds = array_slice($allGuestIds, 0, $maxAllowed);
        \Log::info('👥 Invitados seleccionados', [
            'maxAllowed' => $maxAllowed,
            'allGuestIds' => $allGuestIds,
            'selectedGuestIds' => $this->selectedGuestIds
        ]);
    }

    public function confirm(PoolAccessService $poolAccessService): void
    {
        \Log::info('🟢 🟢 🟢 confirm() LLAMADO 🟢 🟢 🟢', [
            'has_pass' => (bool)$this->pass,
            'has_resident' => (bool)$this->scannedResident,
            'scannedUserId' => $this->scannedUserId,
            'action' => $this->action,
            'poolId' => $this->poolId,
            'selectedGuestIds' => $this->selectedGuestIds
        ]);
        
        if (! $this->pass && ! $this->scannedResident) {
            \Log::error('❌ No hay pass ni residente');
            $this->addError('error', 'Primero escanee un QR.');

            return;
        }

        if ($this->action !== 'entry') {
            \Log::warning('⚠️ Acción no es entry', ['action' => $this->action]);
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
            'selectedResidentId' => 'nullable|integer|exists:residents,id',
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
        $unit = Unit::findOrFail($this->pass->unit_id);
        $pool = Pool::findOrFail($this->poolId);

        // VALIDACIÓN 1: Contar invitados únicos que YA INGRESARON HOY
        $today = now()->toDateString();
        $guestsUsedToday = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereDate('pool_entries.entered_at', $today)
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');

        // Verificar cuántos invitados NUEVOS (no repetidos) se intentan ingresar
        $alreadyEnteredToday = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereDate('pool_entries.entered_at', $today)
            ->whereIn('pool_entry_guests.pool_guest_id', $this->selectedGuestIds)
            ->pluck('pool_entry_guests.pool_guest_id')
            ->unique()
            ->toArray();
        
        $newGuestsCount = count(array_diff($this->selectedGuestIds, $alreadyEnteredToday));
        $totalUniqueTodayAfterEntry = $guestsUsedToday + $newGuestsCount;
        
        // Verificar límite diario según día de semana o fin de semana
        $maxAllowedByRegulation = $this->calculateMaxGuestsAllowedToday();
        $isWeekend = now()->isWeekend();
        $dayType = $isWeekend ? 'fin de semana' : 'día de semana';
        
        if ($totalUniqueTodayAfterEntry > $maxAllowedByRegulation) {
            $this->addError('selectedGuestIds', "REGLAMENTO VIOLADO: Ya usó {$guestsUsedToday} invitados únicos hoy ({$dayType}). Máximo {$maxAllowedByRegulation} permitidos. No se aceptan pagos por invitados extra.");
            return;
        }

        // VALIDACIÓN 2: No más que los precargados
        if ($guestsCount > $this->pass->guests_allowed) {
            $this->addError('selectedGuestIds', 'No puede registrar más invitados que los precargados por el usuario.');

            return;
        }
        
        // VALIDACIÓN 3: Cumplir con el límite mensual (invitados únicos)
        $unit = Unit::findOrFail($this->pass->unit_id);
        $pool = Pool::findOrFail($this->poolId);
        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        // Contar invitados únicos este mes (no suma reingresos)
        $usedThisMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');
        
        $maxGuestsMonth = PoolSetting::get('max_guests_month', 5);
        $availableMonth = max(0, $maxGuestsMonth - $usedThisMonth);
        
        if ($guestsCount > $availableMonth) {
            $this->addError('selectedGuestIds', "LÍMITE MENSUAL EXCEDIDO: Has usado {$usedThisMonth} de {$maxGuestsMonth} invitados únicos este mes. Solo puedes agregar {$availableMonth} invitados más.");
            
            return;
        }

        try {
            /** @var Pool $pool */
            $pool = Pool::findOrFail($this->poolId);

            /** @var Unit $unit */
            $unit = Unit::findOrFail($this->pass->unit_id);

            $entry = null;

            // Si se seleccionó un residente específico, usarlo
            if ($this->selectedResidentId) {
                /** @var Resident $resident */
                $resident = Resident::findOrFail($this->selectedResidentId);
                // Verificar que el residente pertenezca a la unidad del pass
                if ($resident->unit_id !== $this->pass->unit_id) {
                    throw new \Exception('El residente seleccionado no pertenece a esta unidad.');
                }
                $entry = $poolAccessService->registerResidentEntry($pool, $unit, $resident, $guestsCount, now()->toDateTimeString());
            } elseif ($this->pass->resident_id) {
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
            // NO resetear poolId - debe mantenerse con la pileta del turno activo
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

    protected function findOpenEntryForUser(User $user): ?\App\Models\PoolEntry
    {
        $unitId = $user->currentUnitUsers()->first()?->unit_id;
        if (!$unitId) {
            return null;
        }

        return \App\Models\PoolEntry::query()
            ->where('unit_id', $unitId)
            ->where('user_id', $user->id)
            ->whereNull('resident_id')
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();
    }

    protected function confirmResidentEntry(PoolAccessService $poolAccessService): void
    {
        \Log::info('👤 confirmResidentEntry INICIADO', [
            'resident_id' => $this->scannedResident?->id,
            'resident_name' => $this->scannedResident?->name,
            'is_virtual' => $this->scannedResident?->id === null,
            'poolId' => $this->poolId
        ]);
        
        if (! $this->scannedResident) {
            \Log::error('❌ No hay residente escaneado');
            return;
        }

        // Detectar si es un residente virtual (usuario con QR personal)
        $isVirtualResident = $this->scannedResident->id === null;

        if ($isVirtualResident) {
            // Es un usuario con QR personal, usar el método para usuarios
            $this->confirmUserEntry($poolAccessService);
            return;
        }

        // Evitar doble entrada
        $openEntry = $this->findOpenEntryForResident($this->scannedResident);
        if ($openEntry) {
            \Log::warning('⚠️ Residente ya tiene entrada abierta', ['entry_id' => $openEntry->id]);
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
            \Log::info('🏊 Pool encontrado', ['pool_name' => $pool->name]);

            /** @var Unit $unit */
            $unit = Unit::findOrFail($this->scannedResident->unit_id);
            \Log::info('🏠 Unit encontrado', ['unit' => $unit->full_identifier]);

            // Registrar entrada del residente sin invitados
            \Log::info('🟢 Llamando a registerResidentEntry...');
            $entry = $poolAccessService->registerResidentEntry($pool, $unit, $this->scannedResident, 0, now()->toDateTimeString());
            \Log::info('✅ Entrada registrada exitosamente', ['entry_id' => $entry->id]);

            session()->flash('message', 'Ingreso registrado correctamente. Para salir, vuelva a escanear el QR.');

            // Actualizar estado a 'exit' porque ahora está adentro
            $this->action = 'exit';
            // NO resetear poolId - debe mantenerse con la pileta del turno activo
            // Mantenemos scannedResident y token para facilitar la salida
        } catch (\Exception $e) {
            \Log::error('🔴 ERROR al registrar entrada', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('error', $e->getMessage());
        }
    }

    protected function confirmUserEntry(PoolAccessService $poolAccessService): void
    {
        \Log::info('👥 confirmUserEntry INICIADO (QR de usuario)', [
            'scannedUserId' => $this->scannedUserId,
            'poolId' => $this->poolId
        ]);

        if (!$this->scannedUserId) {
            \Log::error('❌ No hay scannedUserId');
            $this->addError('error', 'Error: ID de usuario no encontrado');
            return;
        }

        $user = User::findOrFail($this->scannedUserId);

        // Evitar doble entrada
        $openEntry = $this->findOpenEntryForUser($user);
        if ($openEntry) {
            \Log::warning('⚠️ Usuario ya tiene entrada abierta', ['entry_id' => $openEntry->id]);
            $this->addError('error', 'Este usuario ya está en la pileta. Registre la salida antes de volver a ingresar.');
            return;
        }

        $this->validate([
            'poolId' => 'required|exists:pools,id',
        ], [
            'poolId.required' => 'Debe seleccionar una pileta.',
        ]);

        try {
            $pool = Pool::findOrFail($this->poolId);
            \Log::info('🏊 Pool encontrado', ['pool_name' => $pool->name]);

            $unitUser = $user->currentUnitUsers()->first();
            if (!$unitUser) {
                \Log::error('❌ Usuario sin unidad activa');
                $this->addError('error', 'El usuario no tiene una unidad activa.');
                return;
            }

            $unit = Unit::findOrFail($unitUser->unit_id);
            \Log::info('🏠 Unit encontrado', ['unit' => $unit->full_identifier]);

            // Registrar entrada del usuario sin invitados
            \Log::info('🟢 Llamando a registerEntry (usuario)...');
            $entry = $poolAccessService->registerEntry($pool, $unit, $user, 0, now()->toDateTimeString());
            \Log::info('✅ Entrada registrada exitosamente', ['entry_id' => $entry->id]);

            session()->flash('message', 'Ingreso registrado correctamente. Para salir, vuelva a escanear el QR.');

            $this->action = 'exit';
        } catch (\Exception $e) {
            \Log::error('🔴 ERROR al registrar entrada de usuario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
        if ($this->scannedUserId) {
            // Es un usuario con QR personal
            $user = User::findOrFail($this->scannedUserId);
            $entry = $this->findOpenEntryForUser($user);
        } elseif ($this->scannedResident) {
            $entry = $this->findOpenEntryForResident($this->scannedResident);
        } else {
            $entry = $this->findOpenEntryForPass();
        }

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
        // NO resetear poolId - debe mantenerse con la pileta del turno activo
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

        // Si hay un usuario escaneado, recargar la unit en scannedResident
        if ($this->scannedUserId && $this->scannedResident) {
            $user = User::find($this->scannedUserId);
            if ($user) {
                $unitUser = $user->currentUnitUsers()->first();
                if ($unitUser) {
                    $this->scannedResident->setRelation('unit', Unit::with(['building.complex'])->find($unitUser->unit_id));
                }
            }
        }

        // Calcular límites si hay un pase cargado
        $limitsInfo = null;
        if ($this->pass) {
            $limitsInfo = $this->calculateLimitsInfo();
        }

        // Obtener residentes y usuarios disponibles de la unidad para day-pass
        $availableResidents = [];
        if ($this->pass) {
            $unit = Unit::find($this->pass->unit_id);
            if ($unit) {
                // Obtener residentes activos de la unidad
                $residents = Resident::where('unit_id', $unit->id)
                    ->active()
                    ->orderBy('name')
                    ->get();
                
                // Obtener usuarios activos de la unidad
                $users = $unit->currentUsers()->get();
                $userIds = $users->pluck('id')->toArray();
                
                // Combinar en una lista única
                foreach ($users as $user) {
                    $availableResidents[] = [
                        'type' => 'user',
                        'id' => null, // Los usuarios no tienen resident_id
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->pivot->role ?? 'Usuario',
                    ];
                }
                
                // Agregar residentes que NO sean usuarios (para evitar duplicados)
                foreach ($residents as $resident) {
                    // Solo agregar si el residente no es un usuario de la unidad
                    if (!in_array($resident->user_id, $userIds)) {
                        $availableResidents[] = [
                            'type' => 'resident',
                            'id' => $resident->id,
                            'user_id' => $resident->user_id,
                            'name' => $resident->name,
                            'role' => $resident->relationship ?? 'Residente',
                        ];
                    }
                }
            }
        }

        return view('livewire.banero.pools.scanner', [
            'pool' => $pool,
            'pass' => $this->pass,
            'limitsInfo' => $limitsInfo,
            'availableResidents' => $availableResidents,
        ])->layout('components.layouts.banero', ['title' => 'Escanear QR']);
    }

    protected function calculateLimitsInfo(): array
    {
        if (!$this->pass) {
            return [];
        }

        $unit = Unit::find($this->pass->unit_id);
        if (!$unit) {
            return [];
        }

        $pool = Pool::find($this->poolId);
        if (!$pool) {
            return [];
        }

        $today = now();
        $isWeekend = $today->isWeekend();
        $maxGuestsToday = $isWeekend 
            ? PoolSetting::get('max_guests_weekend', 2) 
            : PoolSetting::get('max_guests_weekday', 4);

        // Contar invitados únicos usados HOY
        $todayStr = $today->toDateString();
        $usedToday = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereDate('pool_entries.entered_at', $todayStr)
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');
        
        $availableToday = max(0, $maxGuestsToday - $usedToday);

        // Calcular invitados únicos usados este mes (no suma reingresos)
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        // Contar invitados únicos este mes
        $usedThisMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');
        
        // Contar invitados únicos usados en FINES DE SEMANA este mes
        $usedWeekendsThisMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) IN (1, 7)') // 1=Domingo, 7=Sábado
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');

        $maxGuestsMonth = PoolSetting::get('max_guests_month', 5);
        $availableMonth = max(0, $maxGuestsMonth - $usedThisMonth);
        
        // Contar cuántos fines de semana quedan este mes (desde hoy)
        $remainingWeekends = 0;
        $current = $today->copy();
        $monthEnd = $today->copy()->endOfMonth();
        
        while ($current <= $monthEnd) {
            if ($current->isWeekend()) {
                $remainingWeekends++;
            }
            $current->addDay();
        }

        return [
            'is_weekend' => $isWeekend,
            'max_guests_today' => $maxGuestsToday,
            'used_today' => $usedToday,
            'available_today' => $availableToday,
            'max_guests_month' => $maxGuestsMonth,
            'used_this_month' => $usedThisMonth,
            'used_weekends_month' => $usedWeekendsThisMonth,
            'available_month' => $availableMonth,
            'remaining_weekends' => $remainingWeekends,
        ];
    }
}
