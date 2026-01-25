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
    /**
     * Token QR único para salida - todos los usuarios deben escanear este QR para salir
     */
    public const EXIT_QR_TOKEN = 'POOL_EXIT_2024';

    public ?\App\Models\PoolShift $activeShift = null;

    /**
     * action: entry | exit | exit_selection (cuando se escanea QR de salida)
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
     * Entrada abierta encontrada durante loadPass() para usar en checkout automático.
     */
    public ?\App\Models\PoolEntry $foundOpenEntry = null;

    /**
     * Lista de entradas abiertas cuando se escanea el QR de salida.
     *
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\PoolEntry>
     */
    public $openEntries = null;

    /**
     * ID de entrada seleccionada para salida.
     */
    public ?int $selectedEntryId = null;

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
        $user = auth()->user();

        // Si es admin, permitir acceso sin turno activo
        if ($user->isAdmin()) {
            // Los admins pueden usar el scanner sin turno activo
            // Si hay múltiples piletas, el admin puede seleccionar una
            $pools = \App\Models\Pool::all();
            if ($pools->count() === 1) {
                $this->poolId = $pools->first()->id;
            }

            // Si hay múltiples piletas, el admin deberá seleccionar una manualmente
            return;
        }

        // Para bañeros, requerir turno activo
        $this->activeShift = \App\Models\PoolShift::getActiveShiftForUser($user->id);

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
            'will_load' => strlen(trim($this->token)) >= 10,
        ]);

        // Autocargar cuando el scanner setea el token manualmente
        if (strlen(trim($this->token)) >= 10) {
            $this->loadPass();
        }
    }

    public function resetScanner(): void
    {
        \Log::info('🔄 resetScanner() - Limpiando estado');

        $this->resetErrorBag();
        $this->token = '';
        $this->pass = null;
        $this->scannedResident = null;
        $this->scannedUserId = null;
        $this->selectedResidentId = null;
        $this->exitNotes = null;
        $this->selectedGuestIds = [];
        $this->showGuestList = false;
        $this->action = 'entry';
        $this->skipUpdatedToken = false; // Asegurar que el flag esté reseteado
        $this->foundOpenEntry = null; // Limpiar entrada encontrada
        $this->openEntries = null; // Limpiar lista de entradas abiertas
        $this->selectedEntryId = null; // Limpiar entrada seleccionada

        // Emitir evento para reiniciar la cámara
        $this->dispatch('restart-camera')->self();
    }

    /**
     * Cargar lista de entradas abiertas cuando se escanea el QR de salida único.
     */
    public function loadExitEntries(): void
    {
        \Log::info('📋 Cargando entradas abiertas para salida', [
            'poolId' => $this->poolId,
        ]);

        // Para admins sin poolId, intentar usar la primera pileta disponible
        if (! $this->poolId) {
            if (auth()->user()->isAdmin()) {
                $firstPool = Pool::first();
                if ($firstPool) {
                    $this->poolId = $firstPool->id;
                } else {
                    $this->addError('error', 'No hay piletas disponibles.');

                    return;
                }
            } else {
                $this->addError('error', 'No hay una pileta seleccionada.');

                return;
            }
        }

        $this->action = 'exit_selection';
        $this->openEntries = \App\Models\PoolEntry::query()
            ->where('pool_id', $this->poolId)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->with(['pool', 'unit.building.complex', 'user', 'resident', 'guests'])
            ->latest('entered_at')
            ->get();

        \Log::info('✅ Entradas abiertas cargadas', [
            'count' => $this->openEntries->count(),
        ]);

        if ($this->openEntries->isEmpty()) {
            $this->addError('error', 'No hay personas dentro de la pileta en este momento.');
            $this->action = 'entry';
        }
    }

    /**
     * Registrar salida de una entrada específica seleccionada.
     */
    public function checkoutSelectedEntry(): void
    {
        if (! $this->selectedEntryId) {
            $this->addError('error', 'Debe seleccionar una persona para registrar la salida.');

            return;
        }

        $entry = \App\Models\PoolEntry::query()
            ->where('id', $this->selectedEntryId)
            ->where('pool_id', $this->poolId)
            ->whereNull('exited_at')
            ->first();

        if (! $entry) {
            $this->addError('error', 'La entrada seleccionada no existe o ya fue cerrada.');

            return;
        }

        \Log::info('✅ Registrando salida de entrada seleccionada', [
            'entry_id' => $entry->id,
            'exited_by_user_id' => auth()->id(),
        ]);

        $entry->update([
            'exited_at' => now(),
            'exited_by_user_id' => auth()->id(),
            'exit_notes' => $this->exitNotes,
        ]);

        // Notificar a otros componentes
        $this->dispatch('entry-registered')->to(Inside::class);

        // Recargar la lista de entradas abiertas
        $this->loadExitEntries();

        // Limpiar selección
        $this->selectedEntryId = null;
        $this->exitNotes = null;

        \Log::info('✅ Salida registrada exitosamente');
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
        \Log::info('📱 loadPassFromScan llamado', [
            'scannedToken' => substr($scannedToken, 0, 20).'...',
            'token_length' => strlen($scannedToken),
            'current_token' => $this->token ? substr($this->token, 0, 20).'...' : '(vacío)',
        ]);

        // Asegurar que el estado esté limpio antes de procesar el nuevo escaneo
        // Esto es crítico después de un checkout automático
        $this->skipUpdatedToken = true;

        // Limpiar estado previo completamente antes de asignar el nuevo token
        $this->resetErrorBag();
        $this->pass = null;
        $this->scannedResident = null;
        $this->scannedUserId = null;
        $this->selectedResidentId = null;
        $this->exitNotes = null;
        $this->selectedGuestIds = [];
        $this->showGuestList = false;
        $this->action = 'entry';
        $this->foundOpenEntry = null; // Limpiar entrada encontrada

        // Asignar el nuevo token después de limpiar el estado
        $this->token = $scannedToken;

        \Log::info('✅ Estado limpiado, procesando nuevo escaneo');
        $this->loadPass();
    }

    public function loadPass(): void
    {
        \Log::info('🔍 loadPass INICIADO', [
            'token' => substr($this->token, 0, 20).'...',
            'token_completo' => $this->token,
            'poolId' => $this->poolId,
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
        $this->foundOpenEntry = null; // Limpiar entrada encontrada
        $this->openEntries = null; // Limpiar lista de entradas abiertas
        $this->selectedEntryId = null; // Limpiar entrada seleccionada

        // DEBUG DETALLADO - Ver exactamente qué llega del scanner
        \Log::info('===== SCAN QR - ANTES DE LIMPIAR =====');
        \Log::info('Token ORIGINAL: "'.$this->token.'"');
        \Log::info('Longitud: '.strlen($this->token));
        \Log::info('Hex completo: '.bin2hex($this->token));

        // Limpieza agresiva del token: trim, minúsculas, remover espacios internos y caracteres de control
        $token = strtolower(trim($this->token));
        $token = preg_replace('/\s+/', '', $token); // Remover todos los espacios
        $token = preg_replace('/[\x00-\x1F\x7F]/u', '', $token); // Remover caracteres de control (invisibles)

        \Log::info('===== SCAN QR - DESPUÉS DE LIMPIAR =====');
        \Log::info('Token LIMPIADO: "'.$token.'"');
        \Log::info('Longitud limpiada: '.strlen($token));
        \Log::info('Hex limpio: '.bin2hex($token));
        \Log::info('========================================');

        if ($token === '') {
            \Log::warning('Token vacío después de limpieza');
            $this->addError('token', 'Debe ingresar o escanear un token.');

            return;
        }

        // Verificar si es el QR único de salida
        $exitToken = strtolower(trim(self::EXIT_QR_TOKEN));
        if ($token === $exitToken) {
            \Log::info('🚪 QR de salida único detectado');
            $this->loadExitEntries();

            return;
        }

        // Intentar buscar primero como QR personal de residente
        $resident = Resident::query()
            ->with(['unit.building.complex', 'user', 'authUser'])
            ->whereRaw('LOWER(qr_token) = ?', [$token])
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

            \Log::info('🎯 Acción determinada para residente', [
                'resident_id' => $resident->id,
                'resident_name' => $resident->name,
                'action' => $this->action,
                'openEntry_exists' => (bool) $openEntry,
                'openEntry_id' => $openEntry?->id,
                'poolId' => $this->poolId,
            ]);

            // Si ya está adentro, ejecutar salida automáticamente
            if ($this->action === 'exit') {
                // Guardar la entrada encontrada para usar en checkout
                $this->foundOpenEntry = $openEntry;

                \Log::info('🚪 Ejecutando checkout automático para residente', [
                    'resident_id' => $resident->id,
                    'resident_name' => $resident->name,
                    'entry_id' => $openEntry?->id,
                ]);

                try {
                    $this->checkout();
                    // El checkout ya limpia todo el estado mediante resetScanner()
                    \Log::info('✅ Checkout automático completado exitosamente, estado limpio para siguiente escaneo');
                } catch (\Exception $e) {
                    \Log::error('❌ Error en checkout automático', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->addError('error', 'Error al registrar la salida: '.$e->getMessage());
                }

                return;
            }

            return;
        }

        // Si no es QR de residente, buscar como QR personal de usuario
        $user = User::query()
            ->whereRaw('LOWER(qr_token) = ?', [$token])
            ->whereNotNull('approved_at')
            ->first();

        if ($user) {
            // Es un QR personal de usuario (propietario/inquilino)
            \Log::info('👥 Usuario con QR personal encontrado', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
            ]);

            $unitUser = $user->currentUnitUsers()->first();
            if (! $unitUser) {
                \Log::error('❌ Usuario no tiene unidad activa');
                $this->addError('token', 'El usuario no tiene una unidad activa asignada.');

                return;
            }

            // Guardar el ID del usuario para usarlo en confirm
            $this->scannedUserId = $user->id;

            // Crear un "residente virtual" con los datos del usuario para mostrar en la UI
            $this->scannedResident = new Resident;
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
                'unit_id' => $unitUser->unit_id,
            ]);

            // Acción automática según estado actual
            $openEntry = $this->findOpenEntryForUser($user);
            $this->action = $openEntry ? 'exit' : 'entry';

            // Si ya está adentro, ejecutar salida automáticamente
            if ($this->action === 'exit') {
                // Guardar la entrada encontrada para usar en checkout
                $this->foundOpenEntry = $openEntry;

                \Log::info('🚪 Ejecutando checkout automático para usuario', [
                    'user_id' => $user->id,
                    'entry_id' => $openEntry?->id,
                ]);

                try {
                    $this->checkout();
                    // El checkout ya limpia todo el estado mediante resetScanner()
                    \Log::info('✅ Checkout automático completado, estado limpio para siguiente escaneo');
                } catch (\Exception $e) {
                    \Log::error('❌ Error en checkout automático para usuario', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->addError('error', 'Error al registrar la salida: '.$e->getMessage());
                }

                return;
            }

            return;
        }

        // Si no es un QR de usuario ni residente, buscar como day-pass
        $pass = PoolDayPass::query()
            ->with(['unit.building.complex', 'user', 'resident', 'guests', 'poolEntry.pool', 'poolEntry.guests'])
            ->whereRaw('LOWER(token) = ?', [$token])
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
            'guests_allowed' => $pass->guests_allowed,
        ]);

        // Acción automática según estado actual
        $openEntry = $this->findOpenEntryForPass();
        $this->action = $openEntry ? 'exit' : 'entry';
        \Log::info('📍 Acción determinada', ['action' => $this->action, 'openEntry_exists' => (bool) $openEntry]);

        // Si ya está adentro y es un day-pass, NO ejecutar checkout automático
        // Dejar que el usuario confirme manualmente la salida para day-passes

        // Por defecto, seleccionar todos los invitados precargados (respetando límite del reglamento)
        $maxAllowed = $this->calculateMaxGuestsAllowedToday();
        $allGuestIds = $pass->guests->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selectedGuestIds = array_slice($allGuestIds, 0, $maxAllowed);
        \Log::info('👥 Invitados seleccionados', [
            'maxAllowed' => $maxAllowed,
            'allGuestIds' => $allGuestIds,
            'selectedGuestIds' => $this->selectedGuestIds,
        ]);
    }

    public function confirm(PoolAccessService $poolAccessService): void
    {
        \Log::info('🟢 🟢 🟢 confirm() LLAMADO 🟢 🟢 🟢', [
            'has_pass' => (bool) $this->pass,
            'has_resident' => (bool) $this->scannedResident,
            'scannedUserId' => $this->scannedUserId,
            'action' => $this->action,
            'poolId' => $this->poolId,
            'selectedGuestIds' => $this->selectedGuestIds,
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

        // VALIDACIÓN 1: Límites mensuales separados por tipo de día
        $today = now();
        $isWeekend = $today->isWeekend();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        if ($isWeekend) {
            // Es fin de semana: validar contra invitados usados en fines de semana del mes
            $usedWeekendsMonth = \DB::table('pool_entry_guests')
                ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
                ->where('pool_entries.unit_id', $unit->id)
                ->where('pool_entries.pool_id', $pool->id)
                ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(pool_entries.entered_at) IN (1, 7)') // 1=Domingo, 7=Sábado
                ->distinct('pool_entry_guests.pool_guest_id')
                ->count('pool_entry_guests.pool_guest_id');

            // Verificar cuántos invitados NUEVOS se intentan ingresar
            $alreadyUsedWeekendsMonth = \DB::table('pool_entry_guests')
                ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
                ->where('pool_entries.unit_id', $unit->id)
                ->where('pool_entries.pool_id', $pool->id)
                ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(pool_entries.entered_at) IN (1, 7)')
                ->whereIn('pool_entry_guests.pool_guest_id', $this->selectedGuestIds)
                ->pluck('pool_entry_guests.pool_guest_id')
                ->unique()
                ->toArray();

            $newGuestsCount = count(array_diff($this->selectedGuestIds, $alreadyUsedWeekendsMonth));
            $maxAllowedMonth = PoolSetting::get('max_guests_weekend', 2);
            $availableMonth = max(0, $maxAllowedMonth - $usedWeekendsMonth);

            if ($newGuestsCount > $availableMonth) {
                $this->addError('selectedGuestIds', "LÍMITE MENSUAL DE FIN DE SEMANA EXCEDIDO: Has usado {$usedWeekendsMonth} de {$maxAllowedMonth} invitados únicos en fines de semana este mes. Solo puedes agregar {$availableMonth} invitados nuevos. Puedes reingresar con los mismos invitados el mismo día.");

                return;
            }
        } else {
            // Es día de semana: validar contra invitados usados en días de semana del mes
            $usedWeekdaysMonth = \DB::table('pool_entry_guests')
                ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
                ->where('pool_entries.unit_id', $unit->id)
                ->where('pool_entries.pool_id', $pool->id)
                ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)') // Lunes=2 a Viernes=6
                ->distinct('pool_entry_guests.pool_guest_id')
                ->count('pool_entry_guests.pool_guest_id');

            // Verificar cuántos invitados NUEVOS se intentan ingresar
            $alreadyUsedWeekdaysMonth = \DB::table('pool_entry_guests')
                ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
                ->where('pool_entries.unit_id', $unit->id)
                ->where('pool_entries.pool_id', $pool->id)
                ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
                ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
                ->whereIn('pool_entry_guests.pool_guest_id', $this->selectedGuestIds)
                ->pluck('pool_entry_guests.pool_guest_id')
                ->unique()
                ->toArray();

            $newGuestsCount = count(array_diff($this->selectedGuestIds, $alreadyUsedWeekdaysMonth));
            $maxAllowedMonth = PoolSetting::get('max_guests_weekday', 4);
            $availableMonth = max(0, $maxAllowedMonth - $usedWeekdaysMonth);

            if ($newGuestsCount > $availableMonth) {
                $this->addError('selectedGuestIds', "LÍMITE MENSUAL DE DÍA DE SEMANA EXCEDIDO: Has usado {$usedWeekdaysMonth} de {$maxAllowedMonth} invitados únicos en días de semana este mes. Solo puedes agregar {$availableMonth} invitados nuevos. Puedes reingresar con los mismos invitados el mismo día.");

                return;
            }
        }

        // VALIDACIÓN 2: No más que los precargados
        if ($guestsCount > $this->pass->guests_allowed) {
            $this->addError('selectedGuestIds', 'No puede registrar más invitados que los precargados por el usuario.');

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

            session()->flash('message', 'Ingreso registrado correctamente.');

            // Resetear completamente para permitir nuevo escaneo
            $this->dispatch('entry-registered')->to(Inside::class);
            $this->resetScanner();
            $this->dispatch('restart-camera')->self();
        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }

    protected function findOpenEntryForPass(): ?\App\Models\PoolEntry
    {
        if (! $this->pass) {
            return null;
        }

        // IMPORTANTE: Limpiar caché de Eloquent para evitar datos obsoletos
        // Esto resuelve el bug donde después de registrar una salida,
        // el siguiente escaneo devolvía la entrada ya cerrada del caché
        \App\Models\PoolEntry::clearBootedModels();

        $q = \App\Models\PoolEntry::query()
            ->where('unit_id', $this->pass->unit_id)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at');

        if ($this->pass->resident_id) {
            // Si el pass es de un residente, buscar por resident_id
            $q->where('resident_id', $this->pass->resident_id);
        } else {
            // Si el pass es de un usuario, buscar por user_id Y sin resident_id
            // (para no confundir con entradas donde un residente usó el QR del usuario)
            $q->where('user_id', $this->pass->user_id)
                ->whereNull('resident_id');
        }

        $entry = $q->latest('entered_at')->first();

        // Si encontramos una entrada, recargarla desde BD y verificar que esté realmente abierta
        if ($entry) {
            $entry->refresh(); // Forzar recarga desde BD
            // Doble verificación: si tiene exited_at, no es una entrada válida
            if ($entry->exited_at !== null) {
                \Log::warning('⚠️ Entrada encontrada pero ya cerrada (caché obsoleto)', [
                    'entry_id' => $entry->id,
                    'exited_at' => $entry->exited_at,
                ]);
                $entry = null;
            }
        }

        return $entry;
    }

    protected function findOpenEntryForResident(Resident $resident): ?\App\Models\PoolEntry
    {
        // IMPORTANTE: Limpiar caché de Eloquent para evitar datos obsoletos
        // Esto resuelve el bug donde después de registrar una salida,
        // el siguiente escaneo devolvía la entrada ya cerrada del caché
        \App\Models\PoolEntry::clearBootedModels();

        $query = \App\Models\PoolEntry::query()
            ->where('unit_id', $resident->unit_id)
            ->where('resident_id', $resident->id)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at');

        // Si hay un poolId del turno activo, filtrar por él también
        if ($this->poolId) {
            $query->where('pool_id', $this->poolId);
        }

        $entry = $query->latest('entered_at')->first();

        // Si encontramos una entrada, recargarla desde BD y verificar que esté realmente abierta
        if ($entry) {
            $entry->refresh(); // Forzar recarga desde BD
            // Doble verificación: si tiene exited_at, no es una entrada válida
            if ($entry->exited_at !== null) {
                \Log::warning('⚠️ Entrada encontrada pero ya cerrada (caché obsoleto)', [
                    'entry_id' => $entry->id,
                    'exited_at' => $entry->exited_at,
                ]);
                $entry = null;
            }
        }

        \Log::info('🔍 Búsqueda de entrada abierta para residente', [
            'resident_id' => $resident->id,
            'resident_name' => $resident->name,
            'unit_id' => $resident->unit_id,
            'pool_id' => $this->poolId,
            'found' => $entry !== null,
            'entry_id' => $entry?->id,
            'entry_pool_id' => $entry?->pool_id,
            'entry_entered_at' => $entry?->entered_at?->toDateTimeString(),
            'entry_exited_at' => $entry?->exited_at?->toDateTimeString(),
        ]);

        return $entry;
    }

    protected function findOpenEntryForUser(User $user): ?\App\Models\PoolEntry
    {
        $unitId = $user->currentUnitUsers()->first()?->unit_id;
        if (! $unitId) {
            \Log::warning('⚠️ Usuario sin unidad activa', ['user_id' => $user->id]);

            return null;
        }

        // IMPORTANTE: Limpiar caché de Eloquent para evitar datos obsoletos
        // Esto resuelve el bug donde después de registrar una salida,
        // el siguiente escaneo devolvía la entrada ya cerrada del caché
        \App\Models\PoolEntry::clearBootedModels();

        $query = \App\Models\PoolEntry::query()
            ->where('unit_id', $unitId)
            ->where('user_id', $user->id)
            ->whereNull('resident_id')
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at');

        // Si hay un poolId del turno activo, filtrar por él también
        if ($this->poolId) {
            $query->where('pool_id', $this->poolId);
        }

        $entry = $query->latest('entered_at')->first();

        // Si encontramos una entrada, recargarla desde BD y verificar que esté realmente abierta
        if ($entry) {
            $entry->refresh(); // Forzar recarga desde BD
            // Doble verificación: si tiene exited_at, no es una entrada válida
            if ($entry->exited_at !== null) {
                \Log::warning('⚠️ Entrada encontrada pero ya cerrada (caché obsoleto)', [
                    'entry_id' => $entry->id,
                    'exited_at' => $entry->exited_at,
                ]);
                $entry = null;
            }
        }

        \Log::info('🔍 Búsqueda de entrada abierta para usuario', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'unit_id' => $unitId,
            'pool_id' => $this->poolId,
            'found' => $entry !== null,
            'entry_id' => $entry?->id,
            'entry_pool_id' => $entry?->pool_id,
            'entry_entered_at' => $entry?->entered_at?->toDateTimeString(),
            'entry_exited_at' => $entry?->exited_at?->toDateTimeString(),
        ]);

        return $entry;
    }

    protected function confirmResidentEntry(PoolAccessService $poolAccessService): void
    {
        \Log::info('👤 confirmResidentEntry INICIADO', [
            'resident_id' => $this->scannedResident?->id,
            'resident_name' => $this->scannedResident?->name,
            'is_virtual' => $this->scannedResident?->id === null,
            'poolId' => $this->poolId,
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

        // Evitar doble entrada - forzar recarga desde BD
        \Log::info('🔍 Verificando entrada abierta...', [
            'unit_id' => $this->scannedResident->unit_id,
            'resident_id' => $this->scannedResident->id,
            'date' => now()->toDateString(),
        ]);

        $openEntry = \App\Models\PoolEntry::query()
            ->where('unit_id', $this->scannedResident->unit_id)
            ->where('resident_id', $this->scannedResident->id)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();

        \Log::info('📊 Resultado búsqueda entrada abierta', [
            'found' => $openEntry !== null,
            'entry_id' => $openEntry?->id,
            'exited_at' => $openEntry?->exited_at,
        ]);

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
            \Log::info('🟢 Llamando a registerResidentEntry...', [
                'pool_id' => $pool->id,
                'unit_id' => $unit->id,
                'resident_id' => $this->scannedResident->id,
                'guests_count' => 0,
            ]);

            $entry = $poolAccessService->registerResidentEntry($pool, $unit, $this->scannedResident, 0, now()->toDateTimeString());

            \Log::info('✅ Entrada registrada exitosamente', ['entry_id' => $entry->id]);

            // Notificar a otros componentes
            $this->dispatch('entry-registered')->to(Inside::class);

            // Usar JavaScript directo para mostrar notificación
            $personName = $this->scannedResident->name;
            $this->js("
                console.log('🔔 Ejecutando notificación de ENTRADA desde backend');
                if (typeof window.showNotification === 'function') {
                    window.showNotification('✅ ENTRADA registrada: {$personName}', 'success', 3000);
                } else {
                    console.error('❌ window.showNotification no está definida');
                    alert('✅ ENTRADA registrada: {$personName}');
                }
            ");

            // Resetear scanner para permitir nuevo escaneo inmediato
            $this->resetScanner();
            $this->dispatch('restart-camera')->self();

            \Log::info('✅ Entrada registrada - cámara reiniciada');

        } catch (\Exception $e) {
            \Log::error('🔴 ERROR al registrar entrada', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('error', $e->getMessage());
        }
    }

    protected function confirmUserEntry(PoolAccessService $poolAccessService): void
    {
        \Log::info('👥 confirmUserEntry INICIADO (QR de usuario)', [
            'scannedUserId' => $this->scannedUserId,
            'poolId' => $this->poolId,
        ]);

        if (! $this->scannedUserId) {
            \Log::error('❌ No hay scannedUserId');
            $this->addError('error', 'Error: ID de usuario no encontrado');

            return;
        }

        $user = User::findOrFail($this->scannedUserId);

        $unitId = $user->currentUnitUsers()->first()?->unit_id;
        if (! $unitId) {
            $this->addError('error', 'El usuario no tiene una unidad activa.');

            return;
        }

        // Evitar doble entrada - forzar recarga desde BD
        $openEntry = \App\Models\PoolEntry::query()
            ->where('unit_id', $unitId)
            ->where('user_id', $user->id)
            ->whereNull('resident_id')
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();

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
            if (! $unitUser) {
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

            // Notificar a otros componentes
            $this->dispatch('entry-registered')->to(Inside::class);

            // Usar JavaScript directo para mostrar notificación
            $personName = $user->name;
            $this->js("
                console.log('🔔 Ejecutando notificación de ENTRADA (usuario) desde backend');
                if (typeof window.showNotification === 'function') {
                    window.showNotification('✅ ENTRADA registrada: {$personName}', 'success', 3000);
                } else {
                    console.error('❌ window.showNotification no está definida');
                    alert('✅ ENTRADA registrada: {$personName}');
                }
            ");

            // Resetear scanner para permitir nuevo escaneo inmediato
            $this->resetScanner();
            $this->dispatch('restart-camera')->self();

            \Log::info('✅ Entrada de usuario registrada - cámara reiniciada');
        } catch (\Exception $e) {
            \Log::error('🔴 ERROR al registrar entrada de usuario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('error', $e->getMessage());
        }
    }

    public function checkout(): void
    {
        \Log::info('🚪 checkout() INICIADO', [
            'has_pass' => (bool) $this->pass,
            'has_scannedResident' => (bool) $this->scannedResident,
            'scannedUserId' => $this->scannedUserId,
            'scannedResident_id' => $this->scannedResident?->id,
            'scannedResident_name' => $this->scannedResident?->name,
            'action' => $this->action,
            'poolId' => $this->poolId,
        ]);

        if (! $this->pass && ! $this->scannedResident) {
            \Log::warning('⚠️ checkout() sin pass ni residente');
            $this->addError('error', 'Primero escanee un QR.');

            return;
        }

        if ($this->action !== 'exit') {
            \Log::warning('⚠️ checkout() con action != exit', ['action' => $this->action]);
            $this->addError('error', 'No hay un ingreso abierto. Registre la entrada.');

            return;
        }

        // Si tenemos una entrada encontrada previamente (checkout automático), usarla
        // De lo contrario, buscar la entrada
        $entry = null;
        if ($this->foundOpenEntry) {
            \Log::info('✅ Usando entrada encontrada previamente', [
                'entry_id' => $this->foundOpenEntry->id,
            ]);
            $entry = $this->foundOpenEntry;
            // Recargar desde BD para asegurar que tenemos los datos más recientes
            $entry = \App\Models\PoolEntry::find($entry->id);
        } elseif ($this->scannedUserId) {
            // Es un usuario con QR personal
            \Log::info('🔍 Buscando entrada para usuario', ['user_id' => $this->scannedUserId]);
            $user = User::findOrFail($this->scannedUserId);
            $entry = $this->findOpenEntryForUser($user);
        } elseif ($this->scannedResident) {
            \Log::info('🔍 Buscando entrada para residente', [
                'resident_id' => $this->scannedResident->id,
                'resident_name' => $this->scannedResident->name,
            ]);
            $entry = $this->findOpenEntryForResident($this->scannedResident);
        } else {
            \Log::info('🔍 Buscando entrada para pass');
            $entry = $this->findOpenEntryForPass();
        }

        \Log::info('📊 Resultado búsqueda entrada', [
            'entry_found' => $entry !== null,
            'entry_id' => $entry?->id,
            'entry_pool_id' => $entry?->pool_id,
            'entry_resident_id' => $entry?->resident_id,
            'entry_user_id' => $entry?->user_id,
            'entry_entered_at' => $entry?->entered_at?->toDateTimeString(),
            'entry_exited_at' => $entry?->exited_at?->toDateTimeString(),
        ]);

        if (! $entry) {
            \Log::error('❌ No se encontró entrada abierta para checkout');
            $this->addError('error', 'No se encontró un ingreso abierto para hacer salida.');

            return;
        }

        \Log::info('✅ Registrando salida', [
            'entry_id' => $entry->id,
            'exited_by_user_id' => auth()->id(),
        ]);

        $entry->update([
            'exited_at' => now(),
            'exited_by_user_id' => auth()->id(),
            'exit_notes' => $this->exitNotes,
        ]);

        \Log::info('✅ Salida registrada en BD', ['entry_id' => $entry->id]);

        // Notificar a otros componentes
        $this->dispatch('entry-registered')->to(Inside::class);

        // Resetear completamente para forzar nuevo escaneo
        // Esto limpia todo el estado incluyendo token, pass, scannedResident, etc.
        $this->resetScanner();

        // Asegurar que el token esté completamente limpio y el flag reseteado
        $this->token = '';
        $this->skipUpdatedToken = false;

        \Log::info('🔄 Estado completamente limpiado después de checkout, listo para siguiente escaneo');

        // Determinar nombre de la persona
        $personName = $entry->resident ? $entry->resident->name : ($entry->user ? $entry->user->name : 'Usuario');

        // Usar JavaScript directo para mostrar notificación
        $this->js("
            console.log('🔔 Ejecutando notificación de SALIDA desde backend');
            if (typeof window.showNotification === 'function') {
                window.showNotification('✅ SALIDA registrada: {$personName}', 'success', 3000);
            } else {
                console.error('❌ window.showNotification no está definida');
                alert('✅ SALIDA registrada: {$personName}');
            }
        ");

        $this->dispatch('restart-camera')->self();

        \Log::info('✅ Salida registrada - cámara reiniciada');
    }

    /**
     * Calcula el límite máximo de invitados según el reglamento para HOY.
     * Lee la configuración dinámica de la base de datos.
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
        // Para bañeros: mostrar la pileta del turno activo
        // Para admins: mostrar la pileta seleccionada o todas las piletas disponibles
        if ($this->activeShift) {
            $pool = Pool::find($this->activeShift->pool_id);
        } elseif (auth()->user()->isAdmin()) {
            // Si es admin y hay poolId seleccionado, usar esa pileta
            $pool = $this->poolId ? Pool::find($this->poolId) : null;
        } else {
            $pool = null;
        }

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
                    // Solo agregar usuarios que tengan nombre válido
                    if (! empty(trim($user->name))) {
                        $availableResidents[] = [
                            'type' => 'user',
                            'id' => null, // Los usuarios no tienen resident_id
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'role' => $user->pivot->role ?? 'Usuario',
                        ];
                    }
                }

                // Agregar residentes que NO sean usuarios (para evitar duplicados)
                foreach ($residents as $resident) {
                    // Solo agregar si el residente no es un usuario de la unidad Y tiene nombre válido
                    if (! in_array($resident->user_id, $userIds) && ! empty(trim($resident->name))) {
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

        // Para admins, obtener todas las piletas disponibles
        $allPools = auth()->user()->isAdmin() ? Pool::all() : collect();

        return view('livewire.banero.pools.scanner', [
            'pool' => $pool,
            'allPools' => $allPools,
            'pass' => $this->pass,
            'limitsInfo' => $limitsInfo,
            'availableResidents' => $availableResidents,
        ])->layout(auth()->user()->isAdmin() ? 'components.layouts.app' : 'components.layouts.banero', ['title' => 'Escanear QR']);
    }

    protected function calculateLimitsInfo(): array
    {
        if (! $this->pass) {
            return [];
        }

        $unit = Unit::find($this->pass->unit_id);
        if (! $unit) {
            return [];
        }

        $pool = Pool::find($this->poolId);
        if (! $pool) {
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
