<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Unidades Funcionales</flux:heading>
            <p class="text-sm text-gray-500 mt-1">Edificio: {{ $building->name }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.buildings.units.create', $building) }}" variant="primary">
                Nueva Unidad
            </flux:button>
            <flux:button href="{{ route('admin.buildings.index') }}" variant="ghost">
                Volver
            </flux:button>
        </div>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por número o piso..." />
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Número</th>
                    <th class="text-left p-2">Piso</th>
                    <th class="text-center p-2">Coeficiente</th>
                    <th class="text-center p-2">Ambientes</th>
                    <th class="text-center p-2">Terrazas</th>
                    <th class="text-center p-2">Área (m²)</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2 font-medium">{{ $unit->number }}</td>
                        <td class="p-2">{{ $unit->floor ?? '-' }}</td>
                        <td class="p-2 text-center">{{ number_format($unit->coefficient, 4) }}</td>
                        <td class="p-2 text-center">{{ $unit->rooms ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $unit->terrazas ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $unit->area ? number_format($unit->area, 2) : '-' }}</td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                <flux:button href="{{ route('admin.buildings.units.edit', [$building, $unit]) }}" variant="ghost" size="sm">
                                    Editar
                                </flux:button>
                                <flux:button wire:click="delete({{ $unit->id }})" 
                                    wire:confirm="¿Está seguro de eliminar esta unidad funcional? Esta acción no se puede deshacer."
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
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            No se encontraron unidades funcionales
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $units->links() }}
    </div>
</div>
