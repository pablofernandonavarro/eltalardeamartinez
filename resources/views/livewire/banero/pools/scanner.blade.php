<div class="max-w-6xl mx-auto">
    {{-- Notificación flotante (JavaScript puro) --}}
    <div id="scanner-notification"
         style="display: none;"
         class="fixed top-4 right-4 z-50 max-w-md w-full transition-all duration-300">
        <div id="notification-content" class="text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3">
            <div id="notification-message" class="flex-1 font-bold text-lg"></div>
            <button onclick="hideNotification()" class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
        </div>
    </div>

    <div class="mb-4 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <flux:heading size="xl">Escanear QR</flux:heading>
            <p class="text-sm text-gray-500 mt-1">Entrada / salida automática.</p>
        </div>

        {{-- Selector de pileta para admins --}}
        @if(auth()->user()->isAdmin() && isset($allPools) && $allPools->count() > 1)
            <div class="flex items-center gap-2">
                <flux:field>
                    <flux:label>Pileta:</flux:label>
                    <flux:select wire:model.live="poolId">
                        <option value="">Seleccionar pileta</option>
                        @foreach($allPools as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        @endif

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                wire:click="resetScanner"
                @click="
                    console.log('🔄 Botón Nuevo clickeado');
                    $wire.resetScanner().then(() => {
                        console.log('✅ Scanner reseteado, reiniciando cámara...');
                        setTimeout(() => {
                            if (window.__baneroStartQrScanner) {
                                console.log('🎥 Llamando a startQrScanner...');
                                window.__baneroStartQrScanner();
                            } else {
                                console.error('❌ window.__baneroStartQrScanner no disponible');
                            }
                        }, 300);
                    });
                "
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
                Nuevo
            </button>
            <flux:button href="{{ route('banero.pools.inside') }}" variant="ghost" wire:navigate>En pileta</flux:button>
            <button 
                type="button" 
                @click="document.getElementById('exitQrModal').classList.remove('hidden')"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
            >
                Ver QR de Salida
            </button>
        </div>
    </div>

    {{-- Modal para mostrar QR de salida --}}
    <div id="exitQrModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" onclick="this.classList.add('hidden')">
        <div class="relative max-w-md w-full bg-white dark:bg-zinc-900 rounded-xl shadow-xl p-6" onclick="event.stopPropagation()">
            <button 
                type="button" 
                onclick="document.getElementById('exitQrModal').classList.add('hidden')" 
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="text-center space-y-4">
                <div>
                    <flux:heading size="lg">QR de Salida</flux:heading>
                    <p class="text-sm text-gray-500 mt-2">Todos los usuarios deben escanear este QR para salir</p>
                </div>

                <div class="flex justify-center">
                    <div class="p-4 bg-white rounded-lg border-2 border-red-500">
                        <div id="exit-qr-code" class="w-64 h-64" wire:ignore></div>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-sm font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded break-all">
                        {{ \App\Livewire\Banero\Pools\Scanner::EXIT_QR_TOKEN }}
                    </div>
                    <p class="text-xs text-gray-500">Token del QR de salida</p>
                </div>

                <flux:callout color="red">
                    <strong>Importante:</strong> Este QR debe estar visible en la salida de la pileta para que los usuarios puedan escanearlo al salir.
                </flux:callout>
            </div>
        </div>
    </div>

    @if($errors->has('error'))
        <flux:callout color="red" class="mb-4">
            {{ $errors->first('error') }}
        </flux:callout>
    @endif

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid gap-4 lg:gap-6 lg:grid-cols-5">
        <!-- Escaneo -->
        <div class="lg:col-span-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="font-semibold">Cámara</div>
                <div class="text-xs text-gray-500">Apuntá al QR</div>
            </div>

            <div class="p-4">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden bg-black/5 dark:bg-white/5">
                    <div wire:ignore id="qr-reader" class="w-full"></div>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-300">Ingresar token manual</summary>
                    <div class="mt-3">
                        <flux:field>
                            <flux:label>Token</flux:label>
                            <div class="flex gap-2">
                                <flux:input wire:model.live="token" placeholder="Pegá el token" class="w-full" />
                                <flux:button type="button" variant="primary" wire:click="loadPass">Buscar</flux:button>
                            </div>
                            <flux:error name="token" />
                            <flux:description>Si no aparece la cámara: permisos del navegador + HTTPS/localhost.</flux:description>
                        </flux:field>
                    </div>
                </details>
            </div>
        </div>

        <!-- Paso 2: Confirmación -->
        <div class="lg:col-span-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="h-7 w-7 rounded-full bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 flex items-center justify-center text-sm font-semibold">2</div>
                        <div class="min-w-0">
                            <div class="font-semibold leading-tight">Confirmación</div>
                            <div class="text-xs text-gray-500">Verificá datos y registrá</div>
                        </div>
                    </div>

                    @if($action === 'exit_selection')
                        <flux:badge color="red">Salida</flux:badge>
                    @elseif($pass || $scannedResident)
                        @if($action === 'entry')
                            <flux:badge color="green">Entrada</flux:badge>
                        @else
                            <flux:badge color="yellow">Salida</flux:badge>
                        @endif
                    @endif
                </div>
            </div>

            <div class="p-4">
                @if($action === 'exit_selection')
                    {{-- Selección de salida cuando se escanea QR único de salida --}}
                    <div class="space-y-4">
                        <flux:callout color="red" icon="door-open">
                            <strong class="text-lg">🚪 QR de Salida Escaneado</strong><br>
                            Seleccioná la persona que está saliendo de la pileta.
                        </flux:callout>

                        @if($openEntries && $openEntries->count() > 0)
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($openEntries as $entry)
                                    @php
                                        $personName = $entry->resident ? $entry->resident->name : ($entry->user ? $entry->user->name : 'N/D');
                                        $personPhoto = $entry->resident ? $entry->resident->profilePhotoUrl() : ($entry->user ? $entry->user->profilePhotoUrl() : null);
                                        $unit = $entry->unit;
                                    @endphp
                                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all {{ $selectedEntryId == $entry->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                        <input 
                                            type="radio" 
                                            name="selectedEntry" 
                                            value="{{ $entry->id }}" 
                                            wire:model="selectedEntryId" 
                                            class="h-5 w-5 text-blue-600"
                                        />
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            @if($personPhoto)
                                                <img src="{{ $personPhoto }}" alt="{{ $personName }}" class="h-12 w-12 rounded-full object-cover flex-shrink-0" />
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold flex-shrink-0">
                                                    {{ \Illuminate\Support\Str::of($personName)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold truncate">{{ $personName }}</div>
                                                <div class="text-sm text-gray-500 truncate">
                                                    {{ $unit->full_identifier }} · {{ $unit->building->complex->name }}
                                                </div>
                                                <div class="mt-1 flex flex-wrap gap-2">
                                                    @if($entry->resident)
                                                        <flux:badge color="blue">Residente</flux:badge>
                                                    @else
                                                        <flux:badge color="gray">Usuario</flux:badge>
                                                    @endif
                                                    @if($entry->guests_count > 0)
                                                        <flux:badge color="green">{{ $entry->guests_count }} invitado(s)</flux:badge>
                                                    @endif
                                                    <flux:badge color="yellow">Ingresó: {{ $entry->entered_at->format('H:i') }}</flux:badge>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <flux:field>
                                <flux:label>Notas (opcional)</flux:label>
                                <flux:textarea rows="2" wire:model="exitNotes" />
                            </flux:field>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button 
                                    class="w-full" 
                                    variant="primary" 
                                    wire:click="checkoutSelectedEntry"
                                    wire:loading.attr="disabled"
                                    :disabled="!$selectedEntryId"
                                >
                                    <span wire:loading.remove>Registrar salida</span>
                                    <span wire:loading>Registrando...</span>
                                </flux:button>
                            </div>
                        @else
                            <flux:callout color="yellow">
                                No hay personas dentro de la pileta en este momento.
                            </flux:callout>
                        @endif
                    </div>
                @elseif(!$pass && !$scannedResident)
                    <div class="rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700 p-6 text-center">
                        <div class="text-sm text-gray-500">Escanea un QR para ver los datos del titular.</div>
                    </div>
                @elseif($scannedResident)
                    {{-- Residente con QR personal --}}
                    @php
                        $photo = $scannedResident->profilePhotoUrl();
                        $name = $scannedResident->name;
                    @endphp

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 mb-4">
                        <div class="flex items-center gap-3">
                            @if($photo)
                                <button type="button" onclick="document.getElementById('photoModal').classList.remove('hidden')" class="flex-shrink-0">
                                    <img src="{{ $photo }}" alt="{{ $name }}" class="h-12 w-12 rounded-full object-cover cursor-pointer hover:opacity-80 transition-opacity" />
                                </button>
                            @else
                                <div class="h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold">
                                    {{ \Illuminate\Support\Str::of($name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="font-semibold truncate">{{ $name }}</div>
                                <div class="text-sm text-gray-500 truncate">
                                    {{ $scannedResident->unit->full_identifier }} · {{ $scannedResident->unit->building->complex->name }}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <flux:badge color="blue">QR Personal</flux:badge>
                                    @if($scannedResident->age)
                                        <flux:badge color="gray">{{ $scannedResident->age }} años</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal para ver foto en grande --}}
                    @if($photo)
                        <div id="photoModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" onclick="this.classList.add('hidden')">
                            <div class="relative max-w-2xl w-full" onclick="event.stopPropagation()">
                                <button type="button" onclick="document.getElementById('photoModal').classList.add('hidden')" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-auto rounded-lg" />
                                <div class="mt-4 text-white text-center">
                                    <div class="font-semibold text-lg">{{ $name }}</div>
                                    <div class="text-sm text-gray-300">{{ $scannedResident->unit->full_identifier }} · {{ $scannedResident->unit->building->complex->name }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($action === 'entry')
                        <form wire:submit="confirm" class="space-y-4">
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 bg-zinc-50 dark:bg-zinc-800">
                                <div class="text-sm text-gray-500 mb-1">Pileta de turno:</div>
                                <div class="font-semibold text-lg">{{ $pool->name ?? 'N/D' }}</div>
                            </div>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button class="w-full" type="submit" variant="primary" wire:loading.attr="disabled">
                                    Registrar ingreso
                                </flux:button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-4">
                            <flux:callout color="yellow" icon="exclamation-triangle">
                                <strong class="text-lg">⚠️ YA ESTÁ ADENTRO</strong><br>
                                Este residente tiene un ingreso abierto. Debe registrar la <b>SALIDA</b>.
                            </flux:callout>

                            <flux:field>
                                <flux:label>Notas (opcional)</flux:label>
                                <flux:textarea rows="2" wire:model="exitNotes" />
                            </flux:field>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <button 
                                    type="button" 
                                    x-data
                                    @click="$wire.checkout().then(() => { console.log('✅ Salida registrada, reiniciando cámara...'); setTimeout(() => { if(window.__baneroStartQrScanner) { console.log('🎥 Ejecutando startQrScanner...'); window.__baneroStartQrScanner(); } else { console.error('❌ window.__baneroStartQrScanner no disponible'); } }, 500); })"
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                >
                                    Registrar salida
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    @php
                        $photo = $pass->resident?->profilePhotoUrl() ?? $pass->user?->profilePhotoUrl();
                        $name = $pass->resident ? $pass->resident->name : ($pass->user?->name ?? 'N/D');
                    @endphp

                    @if($pass->used_at)
                        <flux:callout color="yellow" class="mb-4">
                            Último registro hoy: <b>{{ $pass->used_at->format('H:i') }}</b>. Reingresos permitidos.
                        </flux:callout>
                    @endif

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 mb-4">
                        <div class="flex items-center gap-3">
                            @if($photo)
                                <button type="button" onclick="document.getElementById('photoModalPass').classList.remove('hidden')" class="flex-shrink-0">
                                    <img src="{{ $photo }}" alt="{{ $name }}" class="h-12 w-12 rounded-full object-cover cursor-pointer hover:opacity-80 transition-opacity" />
                                </button>
                            @else
                                <div class="h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold">
                                    {{ \Illuminate\Support\Str::of($name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="font-semibold truncate">{{ $name }}</div>
                                <div class="text-sm text-gray-500 truncate">
                                    {{ $pass->unit->full_identifier }} · {{ $pass->unit->building->complex->name }}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <flux:badge color="gray">Invitados precargados: {{ $pass->guests_allowed }}</flux:badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal para ver foto en grande --}}
                    @if($photo)
                        <div id="photoModalPass" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" onclick="this.classList.add('hidden')">
                            <div class="relative max-w-2xl w-full" onclick="event.stopPropagation()">
                                <button type="button" onclick="document.getElementById('photoModalPass').classList.add('hidden')" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-auto rounded-lg" />
                                <div class="mt-4 text-white text-center">
                                    <div class="font-semibold text-lg">{{ $name }}</div>
                                    <div class="text-sm text-gray-300">{{ $pass->unit->full_identifier }} · {{ $pass->unit->building->complex->name }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($action === 'entry')
                        <form wire:submit="confirm" class="space-y-4">
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 bg-zinc-50 dark:bg-zinc-800 mb-4">
                                <div class="text-sm text-gray-500 mb-1">Pileta de turno:</div>
                                <div class="font-semibold text-lg">{{ $pool->name ?? 'N/D' }}</div>
                            </div>

                            @if($limitsInfo)
                                <flux:callout color="{{ ($limitsInfo['available_today'] <= 0 || $limitsInfo['available_month'] <= 0) ? 'red' : ($limitsInfo['available_today'] <= 1 || $limitsInfo['available_month'] <= 2 ? 'yellow' : 'blue') }}" class="mb-4">
                                    <div class="space-y-2">
                                        <div class="font-bold text-base">Límites de invitados de esta unidad</div>
                                        
                                        <div class="space-y-1">
                                            <div class="font-semibold text-sm">📅 Hoy ({{ $limitsInfo['is_weekend'] ? 'Fin de semana' : 'Día de semana' }})</div>
                                            <div class="text-sm pl-4">
                                                Invitados únicos usados: <span class="font-bold">{{ $limitsInfo['used_today'] }}</span> de {{ $limitsInfo['max_guests_today'] }}<br>
                                                Disponible: <span class="font-bold {{ $limitsInfo['available_today'] <= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $limitsInfo['available_today'] }}</span>
                                            </div>
                                        </div>

                                        <div class="space-y-1">
                                            <div class="font-semibold text-sm">📆 Este mes</div>
                                            <div class="text-sm pl-4">
                                                Invitados únicos usados: <span class="font-bold">{{ $limitsInfo['used_this_month'] }}</span> de {{ $limitsInfo['max_guests_month'] }}<br>
                                                ({{ $limitsInfo['used_weekends_month'] }} usados en fines de semana)<br>
                                                Disponible: <span class="font-bold {{ $limitsInfo['available_month'] <= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $limitsInfo['available_month'] }}</span>
                                            </div>
                                        </div>

                                        <div class="text-xs text-gray-600 dark:text-gray-400 pt-1">
                                            📅 Quedan {{ $limitsInfo['remaining_weekends'] }} días de fin de semana este mes
                                        </div>

                                        @if($limitsInfo['available_today'] <= 0)
                                            <div class="mt-2 pt-2 border-t border-red-300 dark:border-red-700">
                                                <span class="text-red-600 dark:text-red-400 font-bold text-sm">⚠️ LÍMITE DIARIO AGOTADO - No se pueden agregar más invitados hoy</span>
                                            </div>
                                        @elseif($limitsInfo['available_month'] <= 0)
                                            <div class="mt-2 pt-2 border-t border-red-300 dark:border-red-700">
                                                <span class="text-red-600 dark:text-red-400 font-bold text-sm">⚠️ LÍMITE MENSUAL AGOTADO - No se pueden agregar más invitados este mes</span>
                                            </div>
                                        @endif
                                    </div>
                                </flux:callout>
                            @endif

                            {{-- Selector de quién ingresa --}}
                            @if(!empty($availableResidents))
                                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 mb-4">
                                    <flux:field>
                                        <flux:label>¿Quién ingresa? (opcional)</flux:label>
                                        <flux:select wire:model="selectedResidentId">
                                            <option value="">{{ $pass->resident ? $pass->resident->name : $pass->user->name }} (por defecto)</option>
                                            @foreach($availableResidents as $resident)
                                                @if(!empty(trim($resident['name'])) && ($resident['id'] || $pass->user_id !== $resident['user_id']))
                                                    <option value="{{ $resident['id'] ?? '' }}">
                                                        {{ $resident['name'] }} ({{ $resident['role'] }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </flux:select>
                                        <flux:description>Seleccioná quién ingresa físicamente con los invitados</flux:description>
                                    </flux:field>
                                </div>
                            @endif

                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold">Invitados</div>
                                    @if($pass->guests_allowed > 0)
                                        <div class="flex items-center gap-2">
                                            <flux:button type="button" size="sm" variant="ghost" wire:click="selectAllGuests">Todos</flux:button>
                                            <flux:button type="button" size="sm" variant="ghost" wire:click="clearGuests">Ninguno</flux:button>
                                            <flux:button type="button" size="sm" variant="ghost" wire:click="toggleGuestList">
                                                {{ $showGuestList ? 'Ocultar' : 'Editar' }}
                                            </flux:button>
                                        </div>
                                    @endif
                                </div>

                                @if($pass->guests_allowed === 0)
                                    <div class="mt-2 text-sm text-gray-500">Sin invitados precargados.</div>
                                @else
                                    <div class="mt-2 text-sm text-gray-500">
                                        Seleccionados: <b>{{ count($selectedGuestIds ?? []) }}</b> / {{ $pass->guests_allowed }}
                                    </div>
                                    <flux:error name="selectedGuestIds" />

                                    @if($showGuestList)
                                        <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                            @foreach($pass->guests as $guest)
                                                @php $gPhoto = $guest->profilePhotoUrl(); @endphp
                                                <label class="flex items-center gap-3 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                                    <input type="checkbox" value="{{ $guest->id }}" wire:model="selectedGuestIds" class="h-5 w-5" />
                                                    @if($gPhoto)
                                                        <button type="button" onclick="document.getElementById('guestPhotoModal{{ $guest->id }}').classList.remove('hidden'); event.preventDefault()" class="flex-shrink-0">
                                                            <img src="{{ $gPhoto }}" alt="{{ $guest->name }}" class="h-10 w-10 rounded-full object-cover cursor-pointer hover:opacity-80 transition-opacity" />
                                                        </button>
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-semibold">
                                                            {{ \Illuminate\Support\Str::of($guest->name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-medium truncate">{{ $guest->name }}</div>
                                                        <div class="text-xs text-gray-500 truncate">{{ $guest->document_type }} {{ $guest->document_number }}</div>
                                                        @if($guest->birth_date && $guest->birth_date->age < 18)
                                                            <div class="mt-1"><flux:badge color="yellow">Menor ({{ $guest->birth_date->age }} años)</flux:badge></div>
                                                        @endif
                                                    </div>
                                                </label>
                                                {{-- Modal para foto del invitado --}}
                                                @if($gPhoto)
                                                    <div id="guestPhotoModal{{ $guest->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" onclick="this.classList.add('hidden')">
                                                        <div class="relative max-w-2xl w-full" onclick="event.stopPropagation()">
                                                            <button type="button" onclick="document.getElementById('guestPhotoModal{{ $guest->id }}').classList.add('hidden')" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                            <img src="{{ $gPhoto }}" alt="{{ $guest->name }}" class="w-full h-auto rounded-lg" />
                                                            <div class="mt-4 text-white text-center">
                                                                <div class="font-semibold text-lg">{{ $guest->name }}</div>
                                                                <div class="text-sm text-gray-300">{{ $guest->document_type }} {{ $guest->document_number }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Resumen compacto de invitados seleccionados --}}
                            @if(count($selectedGuestIds ?? []) > 0)
                                <div class="rounded-lg border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 p-4">
                                    <div class="font-semibold text-sm text-green-800 dark:text-green-200 mb-2">
                                        👥 Invitados que ingresarán ({{ count($selectedGuestIds) }})
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($pass->guests->whereIn('id', $selectedGuestIds) as $guest)
                                            <div class="flex items-center gap-2 text-sm">
                                                @php $gPhoto = $guest->profilePhotoUrl(); @endphp
                                                @if($gPhoto)
                                                    <img src="{{ $gPhoto }}" alt="{{ $guest->name }}" class="h-8 w-8 rounded-full object-cover" />
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-green-200 dark:bg-green-700 flex items-center justify-center text-xs font-semibold">
                                                        {{ \Illuminate\Support\Str::of($guest->name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                                    </div>
                                                @endif
                                                <div class="flex-1">
                                                    <div class="font-medium text-green-900 dark:text-green-100">{{ $guest->name }}</div>
                                                    <div class="text-xs text-green-700 dark:text-green-300">
                                                        {{ $guest->document_type }} {{ $guest->document_number }}
                                                        @if($guest->birth_date)
                                                            • {{ $guest->birth_date->age }} años
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button class="w-full" type="submit" variant="primary" wire:loading.attr="disabled">
                                    Registrar ingreso
                                </flux:button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-4">
                            <flux:callout color="yellow" icon="exclamation-triangle">
                                <strong class="text-lg">⚠️ YA ESTÁ ADENTRO</strong><br>
                                Este QR tiene un ingreso abierto. Debe registrar la <b>SALIDA</b>.
                            </flux:callout>

                            <flux:field>
                                <flux:label>Notas (opcional)</flux:label>
                                <flux:textarea rows="2" wire:model="exitNotes" />
                            </flux:field>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <button 
                                    type="button" 
                                    x-data
                                    @click="$wire.checkout().then(() => { console.log('✅ Salida registrada, reiniciando cámara...'); setTimeout(() => { if(window.__baneroStartQrScanner) { console.log('🎥 Ejecutando startQrScanner...'); window.__baneroStartQrScanner(); } else { console.error('❌ window.__baneroStartQrScanner no disponible'); } }, 500); })"
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                >
                                    Registrar salida
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
    <script>
        (() => {
            // Importante: con wire:navigate, este script puede ejecutarse múltiples veces.
            // Usamos un guard global para no redeclarar funciones (evita SyntaxError).
            if (window.__baneroQrScannerSetup) return;
            window.__baneroQrScannerSetup = true;

            // Funciones para notificaciones
            let notificationTimeout = null;

            window.showNotification = function(message, type = 'success', duration = 3000) {
                console.log('🔔 showNotification() llamada', {message, type, duration});

                const notification = document.getElementById('scanner-notification');
                const content = document.getElementById('notification-content');
                const messageEl = document.getElementById('notification-message');

                console.log('📍 Elementos encontrados:', {
                    notification: !!notification,
                    content: !!content,
                    messageEl: !!messageEl
                });

                if (!notification || !content || !messageEl) {
                    console.error('⚠️ Elementos de notificación no encontrados');
                    console.log('Elementos en DOM:', {
                        notification: document.getElementById('scanner-notification'),
                        content: document.getElementById('notification-content'),
                        messageEl: document.getElementById('notification-message')
                    });
                    return;
                }

                // Establecer el mensaje
                messageEl.textContent = message;
                console.log('✅ Mensaje establecido:', messageEl.textContent);

                // Establecer el color según el tipo
                content.className = 'text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3';
                if (type === 'success') {
                    content.classList.add('bg-green-500');
                } else if (type === 'error') {
                    content.classList.add('bg-red-500');
                } else if (type === 'warning') {
                    content.classList.add('bg-yellow-500');
                } else {
                    content.classList.add('bg-blue-500');
                }

                console.log('✅ Clases aplicadas:', content.className);

                // Mostrar la notificación
                notification.style.display = 'block';
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';

                console.log('✅ Estilos iniciales aplicados');

                setTimeout(() => {
                    notification.style.opacity = '1';
                    notification.style.transform = 'translateY(0)';
                    console.log('✅ Animación de entrada ejecutada');
                }, 10);

                // Ocultar automáticamente
                clearTimeout(notificationTimeout);
                notificationTimeout = setTimeout(() => {
                    console.log('⏰ Ocultando notificación automáticamente');
                    hideNotification();
                }, duration);

                console.log('📢 Notificación mostrada exitosamente:', message);
            };

            window.hideNotification = function() {
                const notification = document.getElementById('scanner-notification');
                if (!notification) return;

                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            };

            const stopQrScanner = async () => {
                if (!window.__qrInstance) return;

                console.log('🛑 Deteniendo scanner...');
                try {
                    await window.__qrInstance.stop();
                    console.log('✅ Scanner detenido');
                } catch (e) {
                    console.log('⚠️ Error al detener:', e.message);
                }
                try {
                    await window.__qrInstance.clear();
                    console.log('✅ Scanner limpiado');
                } catch (e) {
                    console.log('⚠️ Error al limpiar:', e.message);
                }
                window.__qrInstance = null;

                // Limpiar el elemento del DOM
                const el = document.getElementById('qr-reader');
                if (el) {
                    el.innerHTML = '';
                    console.log('✅ Elemento DOM limpiado');
                }
            };

            const startQrScanner = async () => {
                console.log('🚀 startQrScanner() llamado');
                const el = document.getElementById('qr-reader');
                if (!el) {
                    console.log('❌ Elemento qr-reader no encontrado');
                    return;
                }

                if (typeof Html5Qrcode === 'undefined') {
                    console.log('⏳ Html5Qrcode no cargado, reintentando...');
                    setTimeout(startQrScanner, 200);
                    return;
                }

                console.log('🛑 Deteniendo scanner anterior si existe...');
                await stopQrScanner();

                console.log('🎥 Iniciando nueva instancia de scanner...');
                const qr = new Html5Qrcode('qr-reader');
                window.__qrInstance = qr;

                qr.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 250 },
                    async (decodedText) => {
                        console.log('📦 QR escaneado:', decodedText);

                        // Detener cámara
                        try { await qr.stop(); } catch (e) { console.error('Error deteniendo cámara:', e); }

                        // Llamar al método del componente usando Livewire.find()
                        console.log('📤 Llamando a loadPassFromScan...');
                        try {
                            // Obtener el componente Livewire dinámicamente
                            const componentId = document.getElementById('qr-reader').closest('[wire\\:id]')?.getAttribute('wire:id');
                            if (componentId) {
                                const component = Livewire.find(componentId);
                                if (component) {
                                    // Marcar que estamos esperando una notificación
                                    window.__waitingForNotification = true;
                                    window.__scannedToken = decodedText;

                                    await component.call('loadPassFromScan', decodedText);
                                    console.log('✅ Token procesado correctamente');
                                } else {
                                    console.error('❌ Componente Livewire no encontrado');
                                }
                            } else {
                                console.error('❌ No se pudo obtener el ID del componente');
                            }
                        } catch (err) {
                            console.error('❌ Error procesando token:', err);
                        }
                    },
                    (errorMessage) => {
                        // Error durante el escaneo (se ejecuta continuamente, no loggeamos)
                    }
                ).catch((err) => {
                    console.error('❌ Error al iniciar scanner:', err);
                });
            };

            // Exponer para poder reiniciar desde eventos sin redeclarar
            window.__baneroStartQrScanner = startQrScanner;

            // Esperar a que Livewire esté completamente inicializado
            document.addEventListener('livewire:init', () => {
                console.log('✅ Livewire inicializado, arrancando scanner...');
                // Dar un pequeño delay para asegurar que todo esté listo
                setTimeout(() => {
                    startQrScanner();
                }, 500);
            });

            document.addEventListener('livewire:navigated', () => {
                console.log('✅ Navegación completada, arrancando scanner...');
                setTimeout(() => {
                    startQrScanner();
                }, 300);
            });

            // Al navegar a otra pantalla, frenamos la cámara para evitar "Element not found"
            document.addEventListener('livewire:navigating', () => {
                stopQrScanner();
            });

            document.addEventListener('banero-scanner-reset', () => {
                console.log('🔄 Evento banero-scanner-reset recibido, reiniciando scanner...');
                startQrScanner();
            });
            
            document.addEventListener('restart-qr-scanner', () => {
                console.log('📷 Evento restart-qr-scanner recibido, reiniciando...');
                startQrScanner();
            });
            
            // Escuchar evento de Livewire para reiniciar cámara
            Livewire.on('restart-camera', () => {
                console.log('📷 Evento restart-camera recibido desde Livewire, reiniciando...');
                setTimeout(() => {
                    startQrScanner();
                }, 300);
            });
            
            // También escuchar como evento de window para compatibilidad
            window.addEventListener('restart-camera', () => {
                console.log('📷 Evento restart-camera recibido desde window, reiniciando...');
                setTimeout(() => {
                    startQrScanner();
                }, 300);
            });
            
            // Escuchar cuando Livewire termina cualquier request
            window.addEventListener('livewire:commit', ({ detail }) => {
                console.log('🔍 Livewire commit detectado');
                // Esperar un poco y verificar si no hay QR cargado pero la cámara está detenida
                setTimeout(() => {
                    const hasData = document.querySelector('[wire\\:submit="confirm"]') || 
                                   document.querySelector('[wire\\:click="checkout"]');
                    const hasError = document.querySelector('.text-red-600, .text-red-400');
                    const hasMessage = document.querySelector('[x-data*="message"]');
                    
                    // Si no hay datos cargados, no hay errores visibles, y la cámara está detenida, reiniciar
                    if (!hasData && !hasError && !window.__qrInstance) {
                        console.log('📷 No hay datos ni cámara activa, reiniciando automáticamente...');
                        startQrScanner();
                    } else {
                        console.log('ℹ️ Estado: hasData=', !!hasData, ', hasError=', !!hasError, ', cameraActive=', !!window.__qrInstance);
                    }
                }, 800);
            });
            
            // Escuchar eventos específicos de Livewire usando el hook (TODO EN UNO)
            document.addEventListener('livewire:init', () => {
                console.log('🎬 Livewire:init - Configurando listeners');

                // Listener para restart-camera
                Livewire.on('restart-camera', () => {
                    console.log('📷 Evento restart-camera recibido desde Livewire hook, reiniciando...');
                    setTimeout(() => {
                        startQrScanner();
                    }, 300);
                });

                // Listener para notificaciones
                Livewire.on('show-notification', (event) => {
                    const detail = Array.isArray(event) ? event[0] : event;
                    console.log('🔔 Evento show-notification recibido desde Livewire.on():', detail);

                    // Mostrar notificación visual
                    if (window.showNotification) {
                        window.showNotification(detail.message, detail.type, detail.duration);
                    }

                    // Marcar que ya mostramos la notificación
                    window.__waitingForNotification = false;

                    // Reproducir sonido de beep
                    try {
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        // Configurar el beep
                        oscillator.frequency.value = detail.type === 'success' ? 800 : 400;
                        oscillator.type = 'sine';

                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.2);
                    } catch (err) {
                        console.log('⚠️ Error al reproducir sonido:', err);
                    }
                });

                // Listener para debug de todos los commits
                window.addEventListener('livewire:commit', (event) => {
                    console.log('📦 livewire:commit event:', event.detail);

                    // Si hay efectos JS, ejecutarlos manualmente (para móviles)
                    const effects = event.detail?.component?.effects;
                    if (effects) {
                        console.log('🎪 Effects:', effects);

                        // Ejecutar JavaScript si existe
                        if (effects.js && Array.isArray(effects.js)) {
                            console.log('🎬 Ejecutando JS effects:', effects.js);
                            effects.js.forEach(jsCode => {
                                try {
                                    console.log('Ejecutando:', jsCode);
                                    eval(jsCode);
                                } catch (e) {
                                    console.error('❌ Error ejecutando JS:', e, jsCode);
                                }
                            });
                        }
                    }
                });

                console.log('✅ Todos los listeners de Livewire configurados');
            });

            // Generar QR de salida cuando se abre el modal
            function renderExitQR() {
                const exitToken = @json(\App\Livewire\Banero\Pools\Scanner::EXIT_QR_TOKEN);
                const el = document.getElementById('exit-qr-code');
                
                if (!el) return;

                // Limpiar contenido anterior
                el.innerHTML = '';

                // Esperar a que la librería QRCode esté cargada
                function tryRender() {
                    if (typeof window.QRCode !== 'undefined') {
                        new window.QRCode(el, {
                            text: exitToken,
                            width: 256,
                            height: 256,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: window.QRCode.CorrectLevel.M,
                        });
                    } else {
                        setTimeout(tryRender, 100);
                    }
                }
                tryRender();
            }

            // Renderizar QR cuando se abre el modal
            const exitQrModal = document.getElementById('exitQrModal');
            if (exitQrModal) {
                exitQrModal.addEventListener('click', function(e) {
                    if (e.target === this || e.target.closest('button[onclick*="exitQrModal"]')) {
                        // Solo renderizar cuando se abre el modal (no cuando se cierra)
                        if (!this.classList.contains('hidden')) {
                            setTimeout(renderExitQR, 100);
                        }
                    }
                });
            }

            // También renderizar cuando se hace clic en el botón
            const exitQrButton = document.querySelector('button[onclick*="exitQrModal"]');
            if (exitQrButton) {
                exitQrButton.addEventListener('click', function() {
                    setTimeout(renderExitQR, 200);
                });
            }
        })();
    </script>
</div>
