<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Gestión de Edificios</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Administrá los edificios de cada complejo y sus unidades.</p>
        </div>
        <flux:button href="{{ route('admin.buildings.create') }}" variant="primary" icon="plus">
            Nuevo Edificio
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[200px]">
                <flux:label>Complejo</flux:label>
                <flux:select wire:model.live="complexId" placeholder="Todos los complejos">
                    <option value="">Todos los complejos</option>
                    @foreach($complexes as $complex)
                        <option value="{{ $complex->id }}">{{ $complex->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[220px] flex-1">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o dirección..." icon="magnifying-glass" />
            </flux:field>

            <div class="flex items-end">
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark">Limpiar</flux:button>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Complejo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Nombre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Dirección</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pisos</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidades</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($buildings as $building)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $building->complex->name }}</td>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $building->name }}</td>
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $building->address ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-zinc-900 dark:text-zinc-100">{{ $building->floors ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <flux:badge color="blue">{{ $building->units->count() }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center">
                                    <flux:button href="{{ route('admin.buildings.units.index', $building) }}" variant="ghost" size="sm" icon="building-office">
                                        Unidades
                                    </flux:button>
                                    <flux:button href="{{ route('admin.buildings.edit', $building) }}" variant="ghost" size="sm" icon="pencil-square">
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
                            <td colspan="6" class="px-4 py-12 text-center">
                                <flux:icon.building-office-2 class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron edificios</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $buildings->links() }}
    </div>
</div>
