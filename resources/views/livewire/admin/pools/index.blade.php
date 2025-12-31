<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Registro de Ingresos a Piletas</flux:heading>
        <flux:button href="{{ route('admin.pools.register-entry') }}" variant="primary">
            Registrar Ingreso
        </flux:button>
    </div>

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Pileta</flux:label>
            <flux:select wire:model.live="poolId" placeholder="Todas las piletas">
                <option value="">Todas las piletas</option>
                @foreach($pools as $pool)
                    <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Fecha</flux:label>
            <flux:input type="date" wire:model.live="date" />
        </flux:field>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Ingreso</th>
                    <th class="text-left p-2">Salida</th>
                    <th class="text-left p-2">Pileta</th>
                    <th class="text-left p-2">Unidad</th>
                    <th class="text-left p-2">Titular</th>
                    <th class="text-left p-2">Salida por</th>
                    <th class="text-center p-2">Invitados</th>
                    <th class="text-center p-2">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2 whitespace-nowrap">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                        <td class="p-2 whitespace-nowrap">
                            {{ $entry->exited_at ? $entry->exited_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="p-2">{{ $entry->pool->name }}</td>
                        <td class="p-2">{{ $entry->unit->full_identifier }}</td>
                        <td class="p-2">
                            {{ $entry->resident ? $entry->resident->name : ($entry->user?->name ?? '-') }}
                        </td>
                        <td class="p-2">
                            {{ $entry->exitedBy?->name ?? '-' }}
                        </td>
                        <td class="p-2">
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
                        <td class="p-2 text-center">
                            @if($entry->exited_at)
                                <flux:badge color="gray">Finalizado</flux:badge>
                            @else
                                <flux:badge color="green">En pileta</flux:badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            No se encontraron registros
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
