<div>
    <div class="mb-6">
        <flux:heading size="xl">Mi QR de Pileta</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Tu código QR personal para ingresar a la pileta.
        </p>
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

    @if(!$resident)
        <flux:callout color="yellow">
            No se encontró tu perfil de residente. Contactá al administrador.
        </flux:callout>
    @elseif(!$resident->canHavePersonalQr())
        <flux:callout color="blue">
            Tu cuenta aún no tiene un QR personal asignado. Contactá al responsable de tu unidad.
        </flux:callout>
    @else
        <div class="max-w-2xl mx-auto">
            <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                <div class="flex items-center justify-between mb-6">
                    <flux:heading size="lg">Tu QR Personal</flux:heading>
                    <flux:badge color="green">Permanente</flux:badge>
                </div>

                <div class="flex flex-col items-center gap-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Mostrá este QR al bañero para ingresar a la pileta
                        </p>
                        <p class="text-xs text-gray-500">
                            Unidad: <strong>{{ $resident->unit->full_identifier }}</strong>
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div id="resident-qr-code" class="w-[280px] h-[280px]" wire:ignore></div>
                    </div>

                    <div class="w-full max-w-md">
                        <flux:field>
                            <flux:label>Código (por si no se puede escanear)</flux:label>

                            <div class="flex gap-2 items-stretch">
                                <flux:input 
                                    id="resident-qr-token" 
                                    value="{{ $resident->qr_token }}" 
                                    readonly 
                                    class="flex-1 font-mono text-sm" 
                                />

                                <button
                                    id="resident-qr-copy-btn"
                                    type="button"
                                    class="px-3 border rounded-lg border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                    title="Copiar al portapapeles"
                                >
                                    <flux:icon.document-duplicate variant="outline" class="size-5" />
                                </button>
                            </div>

                            <div id="resident-qr-copy-hint" class="text-xs text-gray-500 mt-1" style="display:none;">
                                Copiado.
                            </div>
                        </flux:field>
                    </div>

                    <div class="flex flex-col items-center gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700 w-full">
                        <button
                            type="button"
                            wire:click="regenerateQr"
                            wire:loading.attr="disabled"
                            class="text-sm px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Regenerar QR
                        </button>
                        <p class="text-xs text-gray-500 text-center">
                            Solo regenerá el QR si crees que fue comprometido
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
    <script>
        (function () {
            let currentToken = @json($resident?->qr_token);

            async function copyTokenToClipboard(token) {
                if (!token) return;

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(token);
                        return;
                    }
                } catch (e) {
                    // fallback
                }

                const input = document.getElementById('resident-qr-token');
                if (input) {
                    input.focus();
                    input.select();
                    document.execCommand('copy');
                }
            }

            function showCopiedHint() {
                const hint = document.getElementById('resident-qr-copy-hint');
                if (!hint) return;
                hint.style.display = 'block';
                setTimeout(() => { hint.style.display = 'none'; }, 1200);
            }

            function bindCopyButton() {
                const btn = document.getElementById('resident-qr-copy-btn');
                if (!btn || btn.__bound) return;
                btn.__bound = true;

                btn.addEventListener('click', async () => {
                    await copyTokenToClipboard(currentToken);
                    showCopiedHint();
                });
            }

            function renderQR(token) {
                const el = document.getElementById('resident-qr-code');
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
                        width: 280,
                        height: 280,
                        correctLevel: window.QRCode.CorrectLevel.M,
                    });
                };

                tryRender();
            }

            function renderFromBlade() {
                renderQR(@json($resident?->qr_token));
            }

            // Initial render
            window.addEventListener('load', () => {
                bindCopyButton();
                renderFromBlade();
            });

            document.addEventListener('livewire:navigated', () => {
                bindCopyButton();
                renderFromBlade();
            });

            // Handle QR regeneration
            function onQrUpdated(event) {
                bindCopyButton();

                const token = event?.detail?.token || null;

                const input = document.getElementById('resident-qr-token');
                if (input && token) {
                    input.value = token;
                }

                renderQR(token);
            }

            window.addEventListener('resident-qr-updated', onQrUpdated);
            document.addEventListener('resident-qr-updated', onQrUpdated);
        })();
    </script>
</div>
