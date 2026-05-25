<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Registro de Ingresos a Piletas</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Historial de ingresos y salidas registrados en las piletas.</p>
        </div>
        <flux:button href="{{ route('admin.pools.register-entry') }}" variant="primary" icon="plus">
            Registrar Ingreso
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[200px]">
                <flux:label>Pileta</flux:label>
                <flux:select wire:model.live="poolId" placeholder="Todas las piletas">
                    <option value="">Todas las piletas</option>
                    @foreach($pools as $pool)
                        <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[180px]">
                <flux:label>Fecha</flux:label>
                <flux:input type="date" wire:model.live="date" />
            </flux:field>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ingreso</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Salida</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pileta</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Titular</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Salida por</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invitados</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-zinc-900 dark:text-zinc-100">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-zinc-700 dark:text-zinc-300">
                                {{ $entry->exited_at ? $entry->exited_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $entry->pool->name }}</td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $entry->unit->full_identifier }}</td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">
                                {{ $entry->resident ? $entry->resident->name : ($entry->user?->name ?? '-') }}
                            </td>
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                {{ $entry->exitedBy?->name ?? '-' }}
                            </td>
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
                            <td class="px-4 py-3 text-center">
                                @if($entry->exited_at)
                                    <flux:badge color="gray">Finalizado</flux:badge>
                                @else
                                    <flux:badge color="green">En pileta</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <flux:icon.beaker class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron registros</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
</div>
