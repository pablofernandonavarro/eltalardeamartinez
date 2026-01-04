<div>
    <div class="mb-6">
        <flux:heading size="xl">Mi QR de Pileta (hoy)</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Precargá la cantidad de invitados para hoy. El bañero podrá elegir cuántos ingresan (hasta ese límite).
        </p>
    </div>

    {{-- Panel de Estado del Reglamento --}}
    @if($limitsInfo['has_limits'])
        <div class="mb-6 p-6 border-2 rounded-xl {{ $limitsInfo['is_weekend'] ? 'border-orange-400 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-950/20 dark:to-amber-950/20' : 'border-blue-400 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/20 dark:to-cyan-950/20' }}">
            <div class="space-y-4">
                {{-- Tipo de Día --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full {{ $limitsInfo['is_weekend'] ? 'bg-orange-500' : 'bg-blue-500' }} flex items-center justify-center text-white text-xl font-bold">
                            {{ $limitsInfo['is_weekend'] ? '🌞' : '💼' }}
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider {{ $limitsInfo['is_weekend'] ? 'text-orange-600 dark:text-orange-400' : 'text-blue-600 dark:text-blue-400' }} font-semibold">
                                {{ $limitsInfo['is_weekend'] ? 'FIN DE SEMANA / FERIADO' : 'DÍA LABORAL (LUNES A VIERNES)' }}
                            </div>
                            <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                                {{ now()->translatedFormat('l, d \d\e F') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cupo Según Reglamento --}}
                <div class="p-4 bg-white dark:bg-zinc-900 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">📋 Cupo Según Reglamento</span>
                        <span class="text-3xl font-black {{ $limitsInfo['is_weekend'] ? 'text-orange-600 dark:text-orange-400' : 'text-blue-600 dark:text-blue-400' }}">
                            {{ $limitsInfo['max_guests_today'] }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $limitsInfo['message'] }}
                    </div>
                </div>

                {{-- Estado de Cumplimiento --}}
                @if($selectedGuestsCount <= $limitsInfo['max_guests_today'])
                    <div class="p-3 bg-green-50 dark:bg-green-950/30 border-2 border-green-500 rounded-lg">
                        <div class="flex items-center gap-2 text-green-700 dark:text-green-400 font-semibold">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>✅ REGLAMENTO RESPETADO: Acceso Concedido</span>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-red-50 dark:bg-red-950/30 border-2 border-red-500 rounded-lg">
                        <div class="flex items-center gap-2 text-red-700 dark:text-red-400 font-semibold">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>⛔ LÍMITE EXCEDIDO: Ajustado automáticamente</span>
                        </div>
                    </div>
                @endif

                <div class="text-xs text-gray-600 dark:text-gray-400 pt-3 border-t border-gray-300 dark:border-gray-700">
                    <strong>⚠️ Importante:</strong> Como anfitrión, debés estar presente obligatoriamente durante toda la permanencia de tus invitados. Los préstamos transitorios de unidad no dan derecho al uso de la pileta. <strong class="text-red-600 dark:text-red-400">No existe la posibilidad de pagar por invitados extra.</strong>
                </div>
            </div>
        </div>
    @endif

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

    @if($units->isEmpty())
        <flux:callout color="blue">
            No tenés unidades asignadas. Contactá al administrador.
        </flux:callout>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            <style>
                /* Mejor UX móvil: checkboxes grandes */
                input[type="checkbox"] { width: 18px; height: 18px; }
            </style>
            <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                <flux:heading size="lg" class="mb-4">Configuración del día</flux:heading>

                <form wire:submit="save" class="space-y-6">
                    <flux:field>
                        <flux:label>Unidad</flux:label>
                        <flux:select wire:model.live="unitId">
                            @foreach($units as $unitUser)
                                <option value="{{ $unitUser->unit_id }}">
                                    {{ $unitUser->unit->full_identifier }}
                                    ({{ $unitUser->unit->building->complex->name }})
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="unitId" />
                    </flux:field>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <flux:label>Invitados precargados (hoy)</flux:label>
                            <flux:button href="{{ route('resident.pools.guests.index') }}" size="sm" variant="ghost" wire:navigate>
                                Administrar invitados
                            </flux:button>
                        </div>

                        @if($guests->isEmpty())
                            <flux:callout color="blue">
                                No tenés invitados cargados para esta unidad. Crealos en “Mis invitados”.
                            </flux:callout>
                        @else
                            <div class="space-y-2 max-h-[320px] overflow-auto">
                                @foreach($guests as $guest)
                                    @php
                                        $isSelected = in_array($guest->id, $selectedGuestIds);
                                        $currentCount = count($selectedGuestIds);
                                        $maxAllowed = $limitsInfo['max_guests_today'] ?? 999;
                                        // Deshabilitar checkbox si: no está seleccionado Y ya se alcanzó el límite
                                        $isDisabled = !$isSelected && $currentCount >= $maxAllowed;
                                    @endphp
                                    <label class="flex items-center gap-3 p-3 border {{ $isDisabled ? 'border-zinc-300 dark:border-zinc-600 bg-zinc-100 dark:bg-zinc-800 opacity-50 cursor-not-allowed' : 'border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800' }} rounded">
                                        <input
                                            type="checkbox"
                                            value="{{ $guest->id }}"
                                            wire:model="selectedGuestIds"
                                            @disabled($isDisabled)
                                        />

                                        @if($guest->profilePhotoUrl())
                                            <img src="{{ $guest->profilePhotoUrl() }}" alt="{{ $guest->name }}" class="h-10 w-10 rounded-full object-cover" />
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold">
                                                {{ \Illuminate\Support\Str::of($guest->name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                            </div>
                                        @endif

                                        <div class="flex-1">
                                            <div class="font-medium">{{ $guest->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $guest->document_type }} {{ $guest->document_number }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="text-sm text-gray-500">
                            Seleccionados: {{ $selectedGuestsCount ?? 0 }}
                        </div>
                    </div>

                    <div class="flex gap-3 items-center">
                        <flux:button type="submit" variant="primary">Guardar</flux:button>

                        <button
                            type="button"
                            wire:click="regenerateToken"
                            wire:loading.attr="disabled"
                            @disabled(! $pass || $pass?->used_at)
                            class="px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Regenera el QR (solo si aún no fue usado)"
                        >
                            Regenerar QR
                        </button>

                        <span wire:loading wire:target="regenerateToken" class="text-sm text-gray-500">Actualizando…</span>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                {{-- QR Personal --}}
                <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">Mi QR Personal</flux:heading>
                        <flux:badge color="green">Permanente</flux:badge>
                    </div>

                    @if(!$currentResident)
                        <flux:callout color="blue">Seleccioná una unidad para ver tu QR personal.</flux:callout>
                    @elseif($currentResident->isMinor())
                        <flux:callout color="yellow">
                            Solo los residentes mayores de 18 años pueden tener un QR personal.
                        </flux:callout>
                    @elseif($currentResident->canHavePersonalQr() && $currentResident->qr_token)
                        <div class="flex flex-col items-center gap-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                Este QR es tuyo y permanente. Usalo para entrar solo a la pileta.
                            </p>

                            <div class="bg-white p-4 rounded-lg">
                                <div id="resident-personal-qr" class="w-[220px] h-[220px]" wire:ignore></div>
                            </div>

                            <div class="w-full">
                                <flux:field>
                                    <flux:label>Código personal</flux:label>

                                    <div class="flex gap-2 items-stretch">
                                        <flux:input id="resident-personal-token" value="{{ $currentResident->qr_token }}" readonly class="flex-1" />

                                        <button
                                            id="resident-personal-copy-btn"
                                            type="button"
                                            class="px-3 border rounded-lg border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            title="Copiar al portapapeles"
                                        >
                                            <flux:icon.document-duplicate variant="outline" class="size-5" />
                                        </button>
                                    </div>

                                    <div id="resident-personal-copy-hint" class="text-xs text-gray-500 mt-1" style="display:none;">
                                        Copiado.
                                    </div>
                                </flux:field>
                            </div>

                            <button
                                type="button"
                                wire:click="regeneratePersonalQr"
                                wire:loading.attr="disabled"
                                class="text-sm px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Regenerar QR personal
                            </button>
                        </div>
                    @endif
                </div>

                {{-- QR Diario --}}
                <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">QR Diario (con invitados)</flux:heading>
                        <div class="text-sm text-gray-500">Invitados: {{ $pass?->guests_allowed ?? 0 }}</div>
                    </div>

                @if(!$pass)
                    <flux:callout color="blue">Seleccioná una unidad para generar el QR.</flux:callout>
                @else
                    @if($pass->used_at)
                        <flux:callout color="yellow" class="mb-4">
                            Este pase ya se usó hoy (último registro: {{ $pass->used_at->format('d/m/Y H:i') }}).
                            Podés reingresar con el mismo QR.
                        </flux:callout>
                    @endif

                    <div class="flex flex-col items-center gap-4">
                        <div class="bg-white p-4 rounded-lg">
                            <div id="resident-daypass-qr" class="w-[220px] h-[220px]" wire:ignore></div>
                        </div>

                        <div class="w-full">
                            <flux:field>
                                <flux:label>Código (por si no se puede escanear)</flux:label>

                                <div class="flex gap-2 items-stretch">
                                    <flux:input id="resident-daypass-token" value="{{ $pass->token }}" readonly class="flex-1" />

                                    <button
                                        id="resident-daypass-copy-btn"
                                        type="button"
                                        class="px-3 border rounded-lg border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        title="Copiar al portapapeles"
                                    >
                                        <flux:icon.document-duplicate variant="outline" class="size-5" />
                                    </button>
                                </div>

                                <div id="resident-daypass-copy-hint" class="text-xs text-gray-500 mt-1" style="display:none;">
                                    Copiado.
                                </div>
                            </flux:field>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
    <script>
        (function () {
            let currentToken = @json($pass?->token);
            let currentPersonalToken = @json($currentResident?->qr_token);

            async function copyTokenToClipboard(token) {
                if (!token) return;

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(token);
                        return;
                    }
                } catch (e) {
                    // fallback abajo
                }

                // Fallback muy compatible
                const input = document.getElementById('resident-daypass-token');
                if (input) {
                    input.focus();
                    input.select();
                    document.execCommand('copy');
                }
            }

            function showCopiedHint() {
                const hint = document.getElementById('resident-daypass-copy-hint');
                if (!hint) return;
                hint.style.display = 'block';
                setTimeout(() => { hint.style.display = 'none'; }, 1200);
            }

            function bindCopyButton() {
                const btn = document.getElementById('resident-daypass-copy-btn');
                if (!btn || btn.__bound) return;
                btn.__bound = true;

                btn.addEventListener('click', async () => {
                    await copyTokenToClipboard(currentToken);
                    showCopiedHint();
                });
            }

            function renderInto(el, token) {
                if (!el) return;

                currentToken = token;

                if (!token) {
                    el.innerHTML = '';
                    return;
                }

                const tryRender = () => {
                    if (typeof window.QRCode === 'undefined') {
                        setTimeout(tryRender, 100);
                        return;
                    }

                    el.innerHTML = '';

                    new window.QRCode(el, {
                        text: token,
                        width: 220,
                        height: 220,
                        correctLevel: window.QRCode.CorrectLevel.M,
                    });
                };

                tryRender();
            }

            function renderFromBladeToken() {
                const el = document.getElementById('resident-daypass-qr');
                renderInto(el, @json($pass?->token));
            }

            // Render inicial
            window.addEventListener('load', () => {
                bindCopyButton();
                renderFromBladeToken();
            });

            // Si se navega con wire:navigate
            document.addEventListener('livewire:navigated', () => {
                bindCopyButton();
                renderFromBladeToken();
            });

            // Render cuando Livewire actualiza el token (guardar / regenerar)
            function onTokenUpdated(event) {
                bindCopyButton();

                const token = event?.detail?.token || null;

                // Actualizar el input visible (Livewire a veces lo re-renderiza)
                const input = document.getElementById('resident-daypass-token');
                if (input && token) {
                    input.value = token;
                }

                const el = document.getElementById('resident-daypass-qr');
                renderInto(el, token);
            }

            // Livewire despacha eventos en window, pero escuchamos también en document por compatibilidad
            window.addEventListener('resident-daypass-qr-updated', onTokenUpdated);
            document.addEventListener('resident-daypass-qr-updated', onTokenUpdated);

            // Personal QR handling
            function bindPersonalCopyButton() {
                const btn = document.getElementById('resident-personal-copy-btn');
                if (!btn || btn.__bound) return;
                btn.__bound = true;

                btn.addEventListener('click', async () => {
                    await copyPersonalTokenToClipboard(currentPersonalToken);
                    showPersonalCopiedHint();
                });
            }

            async function copyPersonalTokenToClipboard(token) {
                if (!token) return;

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(token);
                        return;
                    }
                } catch (e) {
                    // fallback abajo
                }

                const input = document.getElementById('resident-personal-token');
                if (input) {
                    input.focus();
                    input.select();
                    document.execCommand('copy');
                }
            }

            function showPersonalCopiedHint() {
                const hint = document.getElementById('resident-personal-copy-hint');
                if (!hint) return;
                hint.style.display = 'block';
                setTimeout(() => { hint.style.display = 'none'; }, 1200);
            }

            function renderPersonalQR(token) {
                const el = document.getElementById('resident-personal-qr');
                if (!el) return;

                currentPersonalToken = token;

                if (!token) {
                    el.innerHTML = '';
                    return;
                }

                const tryRender = () => {
                    if (typeof window.QRCode === 'undefined') {
                        setTimeout(tryRender, 100);
                        return;
                    }

                    el.innerHTML = '';

                    new window.QRCode(el, {
                        text: token,
                        width: 220,
                        height: 220,
                        correctLevel: window.QRCode.CorrectLevel.M,
                    });
                };

                tryRender();
            }

            function renderPersonalFromBlade() {
                renderPersonalQR(@json($currentResident?->qr_token));
            }

            function onPersonalTokenUpdated(event) {
                bindPersonalCopyButton();

                const token = event?.detail?.token || null;

                const input = document.getElementById('resident-personal-token');
                if (input && token) {
                    input.value = token;
                }

                renderPersonalQR(token);
            }

            // Render personal QR on load
            window.addEventListener('load', () => {
                bindPersonalCopyButton();
                renderPersonalFromBlade();
            });

            document.addEventListener('livewire:navigated', () => {
                bindPersonalCopyButton();
                renderPersonalFromBlade();
            });

            window.addEventListener('resident-personal-qr-updated', onPersonalTokenUpdated);
            document.addEventListener('resident-personal-qr-updated', onPersonalTokenUpdated);
        })();
    </script>
</div>
