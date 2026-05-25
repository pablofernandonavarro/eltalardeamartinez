<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">En pileta</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Personas con ingreso abierto (sin salida registrada).</p>
        </div>
        <flux:button href="{{ route('banero.pools.scanner') }}" variant="primary" icon="qr-code" wire:navigate>
            Escanear QR
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    {{-- KPI cards: misma estructura visual (etiqueta + icono arriba, valor grande, subtexto abajo) y misma altura via items-stretch + flex-col --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 items-stretch">
        {{-- Pileta de turno --}}
        <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="text-sm text-zinc-500">Pileta de turno</div>
                <div class="h-10 w-10 rounded-full bg-cyan-100 dark:bg-cyan-900/20 flex items-center justify-center flex-shrink-0">
                    <flux:icon.beaker class="size-5 text-cyan-600 dark:text-cyan-400" />
                </div>
            </div>
            <div class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white truncate">{{ $pool->name ?? 'N/D' }}</div>
            <div class="mt-auto pt-2 text-xs text-zinc-500">Turno activo</div>
        </div>

        {{-- Personas en pileta --}}
        <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="text-sm text-zinc-500">Personas en pileta</div>
                <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                    <flux:icon.users class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
            <div class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalPeopleCount }}</div>
            <div class="mt-auto pt-2 text-xs text-zinc-500">
                Titulares: {{ $openEntriesCount }} · Invitados: {{ $openGuestsCount }}
            </div>
        </div>

        {{-- Detalle: Menores e Invitados como sub-métricas alineadas horizontalmente --}}
        <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 sm:col-span-2 lg:col-span-1">
            <div class="flex items-start justify-between gap-3">
                <div class="text-sm text-zinc-500">Detalle</div>
                <div class="h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center flex-shrink-0">
                    <flux:icon.user-group class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
            <div class="mt-1 flex items-stretch divide-x divide-zinc-200 dark:divide-zinc-700">
                <div class="flex-1 pr-4">
                    <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $minorsCount }}</div>
                    <div class="text-xs text-zinc-500">Menores</div>
                </div>
                <div class="flex-1 pl-4">
                    <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $openGuestsCount }}</div>
                    <div class="text-xs text-zinc-500">Invitados</div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full max-w-full min-w-0 overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ingreso</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pileta</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Titular</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invitados</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($entries as $entry)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-zinc-900 dark:text-zinc-100">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $entry->pool->name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @php
                                    $photo = $entry->resident?->profilePhotoUrl() ?? $entry->user?->profilePhotoUrl();
                                    $name = $entry->resident ? $entry->resident->name : ($entry->user?->name ?? '-');
                                @endphp
                                @if($photo)
                                    <img src="{{ $photo }}" alt="{{ $name }}" class="h-8 w-8 rounded-full object-cover" />
                                @else
                                    <div class="h-8 w-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-semibold">
                                        {{ \Illuminate\Support\Str::of($name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                    </div>
                                @endif
                                <div class="leading-tight">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $name }}</div>
                                    @if($entry->resident)
                                        <div class="text-xs text-zinc-500">Residente</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $entry->unit->full_identifier }}</td>
                        <td class="px-4 py-3">
                            @if($entry->guests_count > 0)
                                <details class="group">
                                    <summary class="cursor-pointer text-center font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $entry->guests_count }} invitado(s)
                                    </summary>
                                    <div class="mt-2 space-y-1 text-sm">
                                        @foreach($entry->guests as $guest)
                                            <div class="flex items-center gap-2 p-2 rounded bg-zinc-50 dark:bg-zinc-800">
                                                @php $gPhoto = $guest->profilePhotoUrl(); @endphp
                                                @if($gPhoto)
                                                    <img src="{{ $gPhoto }}" alt="{{ $guest->name }}" class="h-6 w-6 rounded-full object-cover" />
                                                @else
                                                    <div class="h-6 w-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-semibold">
                                                        {{ \Illuminate\Support\Str::of($guest->name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-medium truncate">{{ $guest->name }}</div>
                                                    <div class="text-xs text-gray-500 truncate">{{ $guest->document_type }} {{ $guest->document_number }}</div>
                                                </div>
                                                @if($guest->birth_date && $guest->birth_date->age < 18)
                                                    <flux:badge color="yellow" size="sm">{{ $guest->birth_date->age }} años</flux:badge>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                <div class="text-center text-zinc-400">0</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                color="red"
                                type="button"
                                icon="arrow-right-start-on-rectangle"
                                wire:click="checkoutEntry({{ $entry->id }})"
                                wire:confirm="⚠️ IMPORTANTE: Solo usar si la persona salió SIN ESCANEAR su QR.\n\nSi tiene QR personal, pedile que lo escanee en el Scanner.\n\n¿Confirmar salida manual de {{ $name }}?"
                            >
                                Salida manual
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <flux:icon.users class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                            <p class="text-zinc-500 dark:text-zinc-400">No hay personas en pileta.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
</div>
