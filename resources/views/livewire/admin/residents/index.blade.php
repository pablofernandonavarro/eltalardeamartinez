<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Gestión de Residentes</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Administrá los residentes y miembros de cada unidad funcional.</p>
        </div>
        <flux:button href="{{ route('admin.residents.create') }}" variant="primary" icon="plus">
            Nuevo Residente
        </flux:button>
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

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[200px] flex-1">
                <flux:label>Unidad Funcional</flux:label>
                <flux:select wire:model.live="unitId" placeholder="Todas las unidades">
                    <option value="">Todas las unidades</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[160px]">
                <flux:label>Estado</flux:label>
                <flux:select wire:model.live="status" placeholder="Todos los estados">
                    <option value="">Todos los estados</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[200px] flex-1">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Nombre o documento..." icon="magnifying-glass" />
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
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Nombre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad Funcional</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Documento</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Responsable</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Relación</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Edad</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha Inicio</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha Fin</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($residents as $resident)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $resident->name }}</span>
                                    @if($resident->isMinor())
                                        <flux:badge color="yellow" size="sm" class="mt-1 w-fit">Menor</flux:badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $resident->unit->full_identifier }}</span>
                                    @if($resident->unit->building && $resident->unit->building->complex)
                                        <span class="text-sm text-zinc-500">{{ $resident->unit->building->complex->name }}</span>
                                    @else
                                        <span class="text-sm text-zinc-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($resident->document_type && $resident->document_number)
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $resident->document_type }}: {{ $resident->document_number }}</span>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($resident->user)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $resident->user->name }}</span>
                                        <span class="text-xs text-zinc-500">{{ $resident->user->email }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $resident->relationship ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($resident->birth_date)
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $resident->age }} años</span>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-zinc-700 dark:text-zinc-300">
                                @if($resident->started_at)
                                    {{ $resident->started_at->format('d/m/Y') }}
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-zinc-700 dark:text-zinc-300">
                                @if($resident->ended_at)
                                    {{ $resident->ended_at->format('d/m/Y') }}
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($resident->ended_at)
                                    <flux:badge color="gray">Inactivo</flux:badge>
                                @else
                                    <flux:badge color="green">Activo</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center">
                                    <flux:button href="{{ route('admin.residents.edit', $resident) }}" variant="ghost" size="sm" icon="pencil-square">
                                        Editar
                                    </flux:button>
                                    <flux:button wire:click="delete({{ $resident->id }})"
                                        wire:confirm="¿Está seguro de eliminar este residente? Esta acción no se puede deshacer."
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
                            <td colspan="10" class="px-4 py-12 text-center">
                                <flux:icon.user-group class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron residentes</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $residents->links() }}
    </div>
</div>
