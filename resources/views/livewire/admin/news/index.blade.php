<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Gestión de Novedades</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Publicá y ordená las novedades que verán los residentes.</p>
        </div>
        <flux:button href="{{ route('admin.news.create') }}" variant="primary" icon="plus">
            Nueva Novedad
        </flux:button>
    </div>

    @if (session()->has('success'))
        <flux:callout color="green" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="flex-1 min-w-[220px]">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por título o descripción..." icon="magnifying-glass" />
            </flux:field>

            <div class="flex items-end pb-2">
                <flux:checkbox wire:model.live="showDeleted" label="Mostrar eliminadas" />
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Orden</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Título</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha Evento</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Color/Ícono</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Destacada</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($news as $item)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ $item->trashed() ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-300">{{ $item->order }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->title }}</span>
                                    <span class="text-sm text-zinc-500">{{ Str::limit($item->description, 60) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-zinc-700 dark:text-zinc-300">{{ $item->event_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <flux:badge color="{{ $item->color_scheme === 'orange' ? 'yellow' : $item->color_scheme }}">{{ ucfirst($item->color_scheme) }}</flux:badge>
                                    <span class="text-xs text-zinc-500">{{ ucfirst($item->icon_type) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($item->trashed())
                                    <flux:badge color="red">Eliminada</flux:badge>
                                @elseif($item->isPublished())
                                    <flux:badge color="green">Publicada</flux:badge>
                                @else
                                    <flux:badge color="gray">Borrador</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($item->is_featured)
                                    <flux:badge color="yellow" icon="star">Sí</flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
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
                                        <flux:button href="{{ route('admin.news.edit', $item) }}" variant="ghost" size="sm" icon="pencil-square">
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
                            <td colspan="7" class="px-4 py-12 text-center">
                                <flux:icon.newspaper class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron novedades.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $news->links() }}
    </div>
</div>
