<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Edificios</flux:heading>
        <flux:button href="{{ route('admin.buildings.create') }}" variant="primary">
            Nuevo Edificio
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Complejo</flux:label>
            <flux:select wire:model.live="complexId" placeholder="Todos los complejos">
                <option value="">Todos los complejos</option>
                @foreach($complexes as $complex)
                    <option value="{{ $complex->id }}">{{ $complex->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o dirección..." />
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Complejo</th>
                    <th class="text-left p-2">Nombre</th>
                    <th class="text-left p-2">Dirección</th>
                    <th class="text-center p-2">Pisos</th>
                    <th class="text-center p-2">Unidades</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($buildings as $building)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">{{ $building->complex->name }}</td>
                        <td class="p-2 font-medium">{{ $building->name }}</td>
                        <td class="p-2">{{ $building->address ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $building->floors ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $building->units->count() }}</td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                <flux:button href="{{ route('admin.buildings.units.index', $building) }}" variant="ghost" size="sm">
                                    Unidades
                                </flux:button>
                                <flux:button href="{{ route('admin.buildings.edit', $building) }}" variant="ghost" size="sm">
                                    Editar
                                </flux:button>
                                <flux:button wire:click="delete({{ $building->id }})" 
                                    wire:confirm="¿Está seguro de eliminar este edificio? Esta acción no se puede deshacer."
                                    variant="ghost" 
                                    size="sm"
                                    color="red">
                                    Eliminar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            No se encontraron edificios
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $buildings->links() }}
    </div>
</div>
