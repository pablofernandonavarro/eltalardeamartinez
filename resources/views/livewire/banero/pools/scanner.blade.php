<div class="max-w-6xl mx-auto">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <flux:heading size="xl">Escanear QR</flux:heading>
            <p class="text-sm text-gray-500 mt-1">Entrada / salida automática.</p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button type="button" variant="ghost" wire:click="resetScanner">Nuevo</flux:button>
            <flux:button href="{{ route('banero.pools.inside') }}" variant="ghost" wire:navigate>En pileta</flux:button>
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

                    @if($pass || $scannedResident)
                        @if($action === 'entry')
                            <flux:badge color="green">Entrada</flux:badge>
                        @else
                            <flux:badge color="yellow">Salida</flux:badge>
                        @endif
                    @endif
                </div>
            </div>

            <div class="p-4">
                @if(!$pass && !$scannedResident)
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
                                <img src="{{ $photo }}" alt="{{ $name }}" class="h-12 w-12 rounded-full object-cover" />
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

                    @if($action === 'entry')
                        <form wire:submit="confirm" class="space-y-4">
                            <flux:field>
                                <flux:label>Pileta <span class="text-red-500">*</span></flux:label>
                                <flux:select wire:model="poolId" placeholder="Seleccione una pileta">
                                    <option value="">Seleccione una pileta</option>
                                    @foreach($pools as $pool)
                                        <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="poolId" />
                            </flux:field>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button class="w-full" type="submit" variant="primary" wire:loading.attr="disabled">
                                    Registrar ingreso
                                </flux:button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-4">
                            <flux:callout color="green">
                                Este QR tiene un ingreso abierto. Corresponde registrar <b>Salida</b>.
                            </flux:callout>

                            <flux:field>
                                <flux:label>Notas (opcional)</flux:label>
                                <flux:textarea rows="2" wire:model="exitNotes" />
                            </flux:field>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button class="w-full" type="button" variant="primary" wire:click="checkout">
                                    Registrar salida
                                </flux:button>
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
                                <img src="{{ $photo }}" alt="{{ $name }}" class="h-12 w-12 rounded-full object-cover" />
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

                    @if($action === 'entry')
                        <form wire:submit="confirm" class="space-y-4">
                            <flux:field>
                                <flux:label>Pileta <span class="text-red-500">*</span></flux:label>
                                <flux:select wire:model="poolId" placeholder="Seleccione una pileta">
                                    <option value="">Seleccione una pileta</option>
                                    @foreach($pools as $pool)
                                        <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="poolId" />
                            </flux:field>

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
                                                        <img src="{{ $gPhoto }}" alt="{{ $guest->name }}" class="h-10 w-10 rounded-full object-cover" />
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
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button class="w-full" type="submit" variant="primary" wire:loading.attr="disabled">
                                    Registrar ingreso
                                </flux:button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-4">
                            <flux:callout color="green">
                                Este QR tiene un ingreso abierto. Corresponde registrar <b>Salida</b>.
                            </flux:callout>

                            <flux:field>
                                <flux:label>Notas (opcional)</flux:label>
                                <flux:textarea rows="2" wire:model="exitNotes" />
                            </flux:field>

                            <div class="sticky bottom-0 -mx-4 mt-4 border-t border-zinc-200 dark:border-zinc-700 bg-white/95 dark:bg-zinc-900/95 backdrop-blur p-4">
                                <flux:button class="w-full" type="button" variant="primary" wire:click="checkout">
                                    Registrar salida
                                </flux:button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        (() => {
            // Importante: con wire:navigate, este script puede ejecutarse múltiples veces.
            // Usamos un guard global para no redeclarar funciones (evita SyntaxError).
            if (window.__baneroQrScannerSetup) return;
            window.__baneroQrScannerSetup = true;

            const stopQrScanner = async () => {
                if (!window.__qrInstance) return;

                try { await window.__qrInstance.stop(); } catch (e) {}
                try { await window.__qrInstance.clear(); } catch (e) {}
                window.__qrInstance = null;
            };

            const startQrScanner = () => {
                const el = document.getElementById('qr-reader');
                if (!el) return; // si no estamos en la página o aún no está montado

                if (typeof Html5Qrcode === 'undefined') {
                    setTimeout(startQrScanner, 200);
                    return;
                }

                stopQrScanner();

                const qr = new Html5Qrcode('qr-reader');
                window.__qrInstance = qr;

                qr.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 250 },
                    async (decodedText) => {
                        // Evitar múltiples lecturas seguidas
                        try { await qr.stop(); } catch (e) {}

                        @this.set('token', decodedText);
                        @this.call('loadPass');
                    },
                    () => {}
                ).catch(() => {
                    // Silencioso
                });
            };

            // Exponer para poder reiniciar desde eventos sin redeclarar
            window.__baneroStartQrScanner = startQrScanner;

            document.addEventListener('livewire:navigated', () => {
                startQrScanner();
            });

            // Al navegar a otra pantalla, frenamos la cámara para evitar "Element not found"
            document.addEventListener('livewire:navigating', () => {
                stopQrScanner();
            });

            document.addEventListener('banero-scanner-reset', () => {
                startQrScanner();
            });
        })();
    </script>
</div>
