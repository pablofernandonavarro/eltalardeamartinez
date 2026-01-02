<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Amenidades</flux:heading>
            <p class="text-sm text-gray-500 mt-1">
                Gestiona las amenidades que se muestran en la página principal
            </p>
        </div>
        <flux:button href="{{ route('admin.amenities.create') }}" variant="primary" icon="plus" wire:navigate>
            Nueva Amenidad
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <table class="w-full">
            <thead>
                <tr class="border-b bg-zinc-50 dark:bg-zinc-800">
                    <th class="text-left p-3">Orden</th>
                    <th class="text-left p-3">Nombre</th>
                    <th class="text-left p-3">Disponibilidad</th>
                    <th class="text-left p-3">Color</th>
                    <th class="text-center p-3">Estado</th>
                    <th class="text-right p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($amenities as $amenity)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-3">
                            <span class="font-mono text-sm">{{ $amenity->display_order }}</span>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                @php $colors = $amenity->getColorClasses(); @endphp
                                <div class="w-10 h-10 {{ $colors['bg'] }} rounded-full flex items-center justify-center">
                                    {!! $amenity->getIconSvg() !!}
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $amenity->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $amenity->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="text-sm">{{ $amenity->availability }}</span>
                        </td>
                        <td class="p-3">
                            <flux:badge :color="$amenity->icon_color">{{ ucfirst($amenity->icon_color) }}</flux:badge>
                        </td>
                        <td class="p-3 text-center">
                            <button 
                                wire:click="toggleActive({{ $amenity->id }})"
                                type="button"
                                class="inline-flex items-center gap-2"
                            >
                                @if($amenity->is_active)
                                    <flux:badge color="green">Activo</flux:badge>
                                @else
                                    <flux:badge color="zinc">Inactivo</flux:badge>
                                @endif
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button 
                                    href="{{ route('admin.amenities.edit', $amenity) }}"
                                    size="sm" 
                                    variant="ghost"
                                    icon="pencil"
                                    wire:navigate
                                >
                                    Editar
                                </flux:button>
                                <flux:button 
                                    wire:click="delete({{ $amenity->id }})"
                                    wire:confirm="¿Estás seguro de eliminar esta amenidad?"
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                >
                                    Eliminar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            No hay amenidades registradas. 
                            <a href="{{ route('admin.amenities.create') }}" class="text-blue-600 hover:underline" wire:navigate>Crear la primera amenidad</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $amenities->links() }}
    </div>
</div>
