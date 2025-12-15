<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Reglas del Sistema</flux:heading>
        <flux:button href="{{ route('admin.rules.create') }}" variant="primary">
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

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Tipo de Regla</flux:label>
            <flux:select wire:model.live="type" placeholder="Todos los tipos">
                <option value="">Todos los tipos</option>
                @foreach($ruleTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Estado</flux:label>
            <flux:select wire:model.live="status" placeholder="Todos los estados">
                <option value="">Todos los estados</option>
                <option value="active">Activas</option>
                <option value="inactive">Inactivas</option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripción..." />
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Nombre</th>
                    <th class="text-left p-2">Tipo</th>
                    <th class="text-left p-2">Prioridad</th>
                    <th class="text-left p-2">Límites</th>
                    <th class="text-left p-2">Vigencia</th>
                    <th class="text-center p-2">Estado</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $rule->name }}</span>
                                @if($rule->description)
                                    <span class="text-sm text-gray-500">{{ Str::limit($rule->description, 50) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-2">
                            <flux:badge color="blue">{{ $ruleTypes[$rule->type] ?? $rule->type }}</flux:badge>
                        </td>
                        <td class="p-2">{{ $rule->priority }}</td>
                        <td class="p-2">
                            <div class="text-sm">
                                @if($rule->type === 'unit_occupancy')
                                    <span>Máx. habitantes: {{ $rule->limits['max_residents'] ?? 'N/A' }}</span>
                                @elseif($rule->type === 'pool_weekly_guests')
                                    <span>Máx. invitados: {{ $rule->limits['max_guests'] ?? 'N/A' }}</span>
                                    @if(isset($rule->conditions['days_of_week']))
                                        <br><span class="text-gray-500">Días: {{ implode(', ', array_map(fn($d) => ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][$d], $rule->conditions['days_of_week'])) }}</span>
                                    @endif
                                @elseif($rule->type === 'pool_monthly_guests')
                                    <span>Máx. invitados/mes: {{ $rule->limits['max_guests_per_month'] ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-2">
                            <div class="text-sm">
                                @if($rule->valid_from)
                                    <div>Desde: {{ $rule->valid_from->format('d/m/Y') }}</div>
                                @endif
                                @if($rule->valid_to)
                                    <div>Hasta: {{ $rule->valid_to->format('d/m/Y') }}</div>
                                @endif
                                @if(!$rule->valid_from && !$rule->valid_to)
                                    <span class="text-gray-400">Sin límite</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-2 text-center">
                            @if($rule->is_active)
                                <flux:badge color="green">Activa</flux:badge>
                            @else
                                <flux:badge color="gray">Inactiva</flux:badge>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                <flux:button wire:click="toggleActive({{ $rule->id }})" 
                                    variant="ghost" 
                                    size="sm"
                                    color="{{ $rule->is_active ? 'yellow' : 'green' }}">
                                    {{ $rule->is_active ? 'Desactivar' : 'Activar' }}
                                </flux:button>
                                <flux:button href="{{ route('admin.rules.edit', $rule) }}" variant="ghost" size="sm">
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
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            No se encontraron reglas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rules->links() }}
    </div>
</div>
