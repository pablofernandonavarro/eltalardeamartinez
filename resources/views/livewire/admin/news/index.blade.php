<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Novedades</flux:heading>
        <flux:button href="{{ route('admin.news.create') }}" variant="primary">
            Nueva Novedad
        </flux:button>
    </div>

    @if (session()->has('success'))
        <flux:callout color="green" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="mb-6 flex gap-4">
        <flux:field class="flex-1">
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por título o descripción..." />
        </flux:field>

        <div class="flex items-end">
            <flux:checkbox wire:model.live="showDeleted" label="Mostrar eliminadas" />
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Orden</th>
                    <th class="text-left p-2">Título</th>
                    <th class="text-left p-2">Fecha Evento</th>
                    <th class="text-left p-2">Color/Ícono</th>
                    <th class="text-center p-2">Estado</th>
                    <th class="text-center p-2">Destacada</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($news as $item)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors {{ $item->trashed() ? 'opacity-50' : '' }}">
                        <td class="p-2">{{ $item->order }}</td>
                        <td class="p-2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $item->title }}</span>
                                <span class="text-sm text-gray-500">{{ Str::limit($item->description, 60) }}</span>
                            </div>
                        </td>
                        <td class="p-2">{{ $item->event_date->format('d/m/Y') }}</td>
                        <td class="p-2">
                            <div class="flex flex-col gap-1">
                                <flux:badge color="{{ $item->color_scheme === 'orange' ? 'yellow' : $item->color_scheme }}">{{ ucfirst($item->color_scheme) }}</flux:badge>
                                <span class="text-xs text-gray-500">{{ ucfirst($item->icon_type) }}</span>
                            </div>
                        </td>
                        <td class="p-2 text-center">
                            @if($item->trashed())
                                <flux:badge color="red">Eliminada</flux:badge>
                            @elseif($item->isPublished())
                                <flux:badge color="green">Publicada</flux:badge>
                            @else
                                <flux:badge color="gray">Borrador</flux:badge>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($item->is_featured)
                                <flux:badge color="yellow">⭐ Sí</flux:badge>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                @if($item->trashed())
                                    <flux:button wire:click="restore({{ $item->id }})" variant="ghost" size="sm" color="green">
                                        Restaurar
                                    </flux:button>
                                    <flux:button wire:click="forceDelete({{ $item->id }})" 
                                        wire:confirm="¿Eliminar permanentemente esta novedad?"
                                        variant="ghost" 
                                        size="sm"
                                        color="red">
                                        Eliminar
                                    </flux:button>
                                @else
                                    <flux:button wire:click="togglePublish({{ $item->id }})" 
                                        variant="ghost" 
                                        size="sm"
                                        color="{{ $item->isPublished() ? 'yellow' : 'green' }}">
                                        {{ $item->isPublished() ? 'Despublicar' : 'Publicar' }}
                                    </flux:button>
                                    <flux:button href="{{ route('admin.news.edit', $item) }}" variant="ghost" size="sm">
                                        Editar
                                    </flux:button>
                                    <flux:button wire:click="delete({{ $item->id }})" 
                                        wire:confirm="¿Eliminar esta novedad?"
                                        variant="ghost" 
                                        size="sm"
                                        color="red">
                                        Eliminar
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            No se encontraron novedades.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $news->links() }}
    </div>
</div>
