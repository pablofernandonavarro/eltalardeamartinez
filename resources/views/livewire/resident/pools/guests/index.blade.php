<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Mis invitados</flux:heading>
            <p class="text-sm text-gray-500 mt-1">Cargá aquí las personas que vas a invitar a la pileta.</p>
        </div>
        <flux:button href="{{ route('resident.pools.guests.create') }}" variant="primary" wire:navigate>
            Nuevo invitado
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if($unitUsers->isEmpty())
        <flux:callout color="blue">No tenés unidades asignadas.</flux:callout>
    @else
        <div class="mb-4">
            <flux:field>
                <flux:label>Unidad</flux:label>
                <flux:select wire:model.live="unitId">
                    @foreach($unitUsers as $unitUser)
                        <option value="{{ $unitUser->unit_id }}">
                            {{ $unitUser->unit->full_identifier }} ({{ $unitUser->unit->building->complex->name }})
                        </option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-3">Nombre</th>
                        <th class="text-left p-3">Documento</th>
                        <th class="text-left p-3">Teléfono</th>
                        <th class="text-right p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guests as $guest)
                        <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                            <td class="p-3 font-medium">{{ $guest->name }}</td>
                            <td class="p-3">
                                @if($guest->document_type || $guest->document_number)
                                    {{ $guest->document_type }} {{ $guest->document_number }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $guest->phone ?? '-' }}</td>
                            <td class="p-3">
                                <div class="flex justify-end gap-2">
                                    <flux:button size="sm" variant="ghost" href="{{ route('resident.pools.guests.edit', $guest) }}" wire:navigate>
                                        Editar
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        type="button"
                                        wire:click="delete({{ $guest->id }})"
                                        wire:confirm="¿Eliminar invitado?"
                                    >
                                        Eliminar
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-500">
                                No hay invitados cargados para esta unidad.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
