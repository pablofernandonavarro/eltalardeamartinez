<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">En pileta</flux:heading>
            <p class="text-sm text-gray-500 mt-1">Personas con ingreso abierto (sin salida registrada).</p>
        </div>
        <flux:button href="{{ route('banero.pools.scanner') }}" variant="ghost" wire:navigate>
            Escanear QR
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="mb-4 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 bg-zinc-50 dark:bg-zinc-800">
        <div class="text-sm text-gray-500 mb-1">Pileta de turno:</div>
        <div class="font-semibold text-lg">{{ $pool->name ?? 'N/D' }}</div>
    </div>

    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="text-sm text-gray-500">Personas en pileta</div>
            <div class="mt-1 text-3xl font-semibold">{{ $totalPeopleCount }}</div>
            <div class="mt-1 text-xs text-gray-500">
                Titulares: {{ $openEntriesCount }} · Invitados: {{ $openGuestsCount }}
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <div class="text-sm text-gray-500">Detalle</div>
            <div class="mt-2 flex items-end justify-between gap-6">
                <div>
                    <div class="text-xs text-gray-500">Menores</div>
                    <div class="mt-1 text-2xl font-semibold">{{ $minorsCount }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Invitados</div>
                    <div class="mt-1 text-2xl font-semibold">{{ $openGuestsCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full max-w-full min-w-0 overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <table class="w-full min-w-[900px]">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-3">Ingreso</th>
                    <th class="text-left p-3">Pileta</th>
                    <th class="text-left p-3">Titular</th>
                    <th class="text-left p-3">Unidad</th>
                    <th class="text-center p-3">Invitados</th>
                    <th class="text-right p-3">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-3 whitespace-nowrap">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                        <td class="p-3">{{ $entry->pool->name }}</td>
                        <td class="p-3">
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
                                    <div class="font-medium">{{ $name }}</div>
                                    @if($entry->resident)
                                        <div class="text-xs text-gray-500">Residente</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-3">{{ $entry->unit->full_identifier }}</td>
                        <td class="p-3">
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
                                <div class="text-center text-gray-400">0</div>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                type="button"
                                wire:click="checkoutEntry({{ $entry->id }})"
                                wire:confirm="⚠️ IMPORTANTE: Solo usar si la persona salió SIN ESCANEAR su QR.\n\nSi tiene QR personal, pedile que lo escanee en el Scanner.\n\n¿Confirmar salida manual de {{ $name }}?"
                            >
                                Salida manual
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            No hay personas en pileta.
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
