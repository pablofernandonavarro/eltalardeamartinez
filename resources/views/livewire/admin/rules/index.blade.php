<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Gestión de Reglas del Sistema</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Reglas de ocupación de unidades y límites de invitados a piletas.</p>
        </div>
        <flux:button href="{{ route('admin.rules.create') }}" variant="primary" icon="plus">
            Nueva Regla
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
            <flux:field class="min-w-[200px]">
                <flux:label>Tipo de Regla</flux:label>
                <flux:select wire:model.live="type" placeholder="Todos los tipos">
                    <option value="">Todos los tipos</option>
                    @foreach($ruleTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[160px]">
                <flux:label>Estado</flux:label>
                <flux:select wire:model.live="status" placeholder="Todos los estados">
                    <option value="">Todos los estados</option>
                    <option value="active">Activas</option>
                    <option value="inactive">Inactivas</option>
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[220px] flex-1">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripción..." icon="magnifying-glass" />
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
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tipo</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Prioridad</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Límites</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vigencia</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $rule->name }}</span>
                                    @if($rule->description)
                                        <span class="text-sm text-zinc-500">{{ Str::limit($rule->description, 50) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="blue">{{ $ruleTypes[$rule->type] ?? $rule->type }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-center text-zinc-900 dark:text-zinc-100">{{ $rule->priority }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                    @if($rule->type === 'unit_occupancy')
                                        <span>Máx. habitantes: {{ $rule->limits['max_residents'] ?? 'N/A' }}</span>
                                    @elseif($rule->type === 'pool_weekly_guests')
                                        <span>Máx. invitados: {{ $rule->limits['max_guests'] ?? 'N/A' }}</span>
                                        @if(isset($rule->conditions['days_of_week']))
                                            <br><span class="text-zinc-500">Días: {{ implode(', ', array_map(fn($d) => ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][$d], $rule->conditions['days_of_week'])) }}</span>
                                        @endif
                                    @elseif($rule->type === 'pool_monthly_guests')
                                        <span>Máx. invitados/mes: {{ $rule->limits['max_guests_per_month'] ?? 'N/A' }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                    @if($rule->valid_from)
                                        <div>Desde: {{ $rule->valid_from->format('d/m/Y') }}</div>
                                    @endif
                                    @if($rule->valid_to)
                                        <div>Hasta: {{ $rule->valid_to->format('d/m/Y') }}</div>
                                    @endif
                                    @if(!$rule->valid_from && !$rule->valid_to)
                                        <span class="text-zinc-400">Sin límite</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($rule->is_active)
                                    <flux:badge color="green">Activa</flux:badge>
                                @else
                                    <flux:badge color="gray">Inactiva</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center">
                                    <flux:button wire:click="toggleActive({{ $rule->id }})"
                                        variant="ghost"
                                        size="sm"
                                        color="{{ $rule->is_active ? 'yellow' : 'green' }}">
                                        {{ $rule->is_active ? 'Desactivar' : 'Activar' }}
                                    </flux:button>
                                    <flux:button href="{{ route('admin.rules.edit', $rule) }}" variant="ghost" size="sm" icon="pencil-square">
                                        Editar
                                    </flux:button>
                                    <flux:button wire:click="delete({{ $rule->id }})"
                                        wire:confirm="¿Está seguro de eliminar esta regla? Esta acción no se puede deshacer."
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
                            <td colspan="7" class="px-4 py-12 text-center">
                                <flux:icon.document-text class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron reglas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $rules->links() }}
    </div>
</div>
