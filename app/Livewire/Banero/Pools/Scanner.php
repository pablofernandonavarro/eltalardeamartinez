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

        $this->loadPass();
    }

    public function loadPass(): void
    {
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

        $token = strtolower(trim($this->token));
        $token = preg_replace('/\s+/', '', $token);
        $token = preg_replace('/[\x00-\x1F\x7F]/u', '', $token);

        if ($token === '') {
            $this->addError('token', 'Debe ingresar o escanear un token.');

            return;
        }

        // Verificar si es el QR único de salida
        $exitToken = strtolower(trim(self::EXIT_QR_TOKEN));
        if ($token === $exitToken) {
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

            if ($this->action === 'exit') {
                $this->foundOpenEntry = $openEntry;

                try {
                    $personName = $resident->name;
                    $this->checkout();
                    $this->js("alert('Usuario salió: {$personName}');");
                } catch (\Exception $e) {
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
            $unitUser = $user->currentUnitUsers()->first();
            if (! $unitUser) {
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

            $openEntry = $this->findOpenEntryForUser($user);
            $this->action = $openEntry ? 'exit' : 'entry';

            if ($this->action === 'exit') {
                $this->foundOpenEntry = $openEntry;

                try {
                    $personName = $user->name;
                    $this->checkout();
                    $this->js("alert('Usuario salió: {$personName}');");
                } catch (\Exception $e) {
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
            $this->addError('token', 'El pase no corresponde a hoy.');

            return;
        }

        $this->pass = $pass;

        $openEntry = $this->findOpenEntryForPass();
        $this->action = $openEntry ? 'exit' : 'entry';

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

        if ($entry) {
            $entry->refresh();
            if ($entry->exited_at !== null) {
                $entry = null;
            }
        }

        return $entry;
    }

    protected function findOpenEntryForUser(User $user): ?\App\Models\PoolEntry
    {
        $unitId = $user->currentUnitUsers()->first()?->unit_id;
        if (! $unitId) {
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

        if ($entry) {
            $entry->refresh();
            if ($entry->exited_at !== null) {
                $entry = null;
            }
        }

        return $entry;
    }

    protected function confirmResidentEntry(PoolAccessService $poolAccessService): void
    {
        if (! $this->scannedResident) {
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
        $openEntry = \App\Models\PoolEntry::query()
            ->where('unit_id', $this->scannedResident->unit_id)
            ->where('resident_id', $this->scannedResident->id)
            ->whereDate('entered_at', now()->toDateString())
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();

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

            $entry = $poolAccessService->registerResidentEntry($pool, $unit, $this->scannedResident, 0, now()->toDateTimeString());

            $this->dispatch('entry-registered')->to(Inside::class);

            $personName = $this->scannedResident->name;
            $this->js("alert('Usuario ingresó: {$personName}');");

            $this->resetScanner();
            $this->dispatch('restart-camera')->self();

        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }

    protected function confirmUserEntry(PoolAccessService $poolAccessService): void
    {
        if (! $this->scannedUserId) {
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

            $unitUser = $user->currentUnitUsers()->first();
            if (! $unitUser) {
                $this->addError('error', 'El usuario no tiene una unidad activa.');

                return;
            }

            $unit = Unit::findOrFail($unitUser->unit_id);

            $entry = $poolAccessService->registerEntry($pool, $unit, $user, 0, now()->toDateTimeString());

            $this->dispatch('entry-registered')->to(Inside::class);

            $personName = $user->name;
            $this->js("alert('Usuario ingresó: {$personName}');");

            $this->resetScanner();
            $this->dispatch('restart-camera')->self();

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

        $entry = null;
        if ($this->foundOpenEntry) {
            $entry = $this->foundOpenEntry;
            // Reload from DB to ensure fresh data
            $entry = \App\Models\PoolEntry::find($entry->id);
        } elseif ($this->scannedUserId) {
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

        $this->dispatch('entry-registered')->to(Inside::class);

        $this->resetScanner();

        $this->token = '';
        $this->skipUpdatedToken = false;

        $personName = $entry->resident ? $entry->resident->name : ($entry->user ? $entry->user->name : 'Usuario');

        $this->js("alert('Usuario salió: {$personName}');");

        $this->dispatch('restart-camera')->self();
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

        // Contar invitados únicos usados en DÍAS DE SEMANA este mes
        $usedWeekdaysThisMonth = \DB::table('pool_entry_guests')
            ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
            ->where('pool_entries.unit_id', $unit->id)
            ->where('pool_entries.pool_id', $pool->id)
            ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
            ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)') // Lunes a Viernes
            ->distinct('pool_entry_guests.pool_guest_id')
            ->count('pool_entry_guests.pool_guest_id');

        // Límites mensuales según tipo de día
        $maxGuestsWeekdayMonth = PoolSetting::get('max_guests_month', 5);
        $maxGuestsWeekendMonth = PoolSetting::get('max_guests_weekend_month', 3);
        $availableWeekdayMonth = max(0, $maxGuestsWeekdayMonth - $usedWeekdaysThisMonth);
        $availableWeekendMonth = max(0, $maxGuestsWeekendMonth - $usedWeekendsThisMonth);

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
            // Límites de días de semana
            'max_guests_weekday_month' => $maxGuestsWeekdayMonth,
            'used_weekdays_month' => $usedWeekdaysThisMonth,
            'available_weekday_month' => $availableWeekdayMonth,
            // Límites de fines de semana
            'max_guests_weekend_month' => $maxGuestsWeekendMonth,
            'used_weekends_month' => $usedWeekendsThisMonth,
            'available_weekend_month' => $availableWeekendMonth,
            'remaining_weekends' => $remainingWeekends,
        ];
    }
}
