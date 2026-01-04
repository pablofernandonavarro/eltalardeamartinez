<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Unidad Funcional: {{ $unit->full_identifier }}</flux:heading>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.units.edit', $unit) }}" variant="primary">
                Editar
            </flux:button>
            <flux:button wire:click="delete"
                wire:confirm="¿Está seguro de eliminar esta unidad funcional? Esta acción no se puede deshacer."
                variant="ghost"
                color="red">
                Eliminar
            </flux:button>
        </div>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información General -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Información General</flux:heading>
            </div>
            <div class="p-4 space-y-4">
                <div>
                    <span class="text-sm text-gray-500">Edificio:</span>
                    <p class="font-medium">{{ $unit->building->name ?? 'N/A' }}</p>
                </div>
                @if($unit->building && $unit->building->complex)
                    <div>
                        <span class="text-sm text-gray-500">Complejo:</span>
                        <p class="font-medium">{{ $unit->building->complex->name }}</p>
                    </div>
                @endif
                <div>
                    <span class="text-sm text-gray-500">Número:</span>
                    <p class="font-medium">{{ $unit->number }}</p>
                </div>
                @if($unit->floor)
                    <div>
                        <span class="text-sm text-gray-500">Piso:</span>
                        <p class="font-medium">{{ $unit->floor }}</p>
                    </div>
                @endif
                <div>
                    <span class="text-sm text-gray-500">Coeficiente:</span>
                    <p class="font-medium">{{ number_format($unit->coefficient, 4) }}</p>
                </div>
                @if($unit->area)
                    <div>
                        <span class="text-sm text-gray-500">Área:</span>
                        <p class="font-medium">{{ number_format($unit->area, 2) }} m²</p>
                    </div>
                @endif
                @if($unit->rooms)
                    <div>
                        <span class="text-sm text-gray-500">Ambientes:</span>
                        <p class="font-medium">{{ $unit->rooms }}</p>
                    </div>
                @endif
                @if($unit->terrazas)
                    <div>
                        <span class="text-sm text-gray-500">Terrazas:</span>
                        <p class="font-medium">{{ $unit->terrazas }}</p>
                    </div>
                @endif
                @if($unit->notes)
                    <div>
                        <span class="text-sm text-gray-500">Notas:</span>
                        <p class="font-medium">{{ $unit->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Propietario e Inquilino -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <flux:heading size="lg">Asignaciones</flux:heading>
            </div>
            <div class="p-4 space-y-4">
                @if($owner && $owner->user)
                    <div>
                        <span class="text-sm text-gray-500">Propietario:</span>
                        <div class="mt-1">
                            <p class="font-medium">{{ $owner->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $owner->user->email }}</p>
                            @if($owner->is_responsible)
                                <flux:badge color="blue" size="sm" class="mt-1">Responsable del Pago</flux:badge>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">Desde: {{ $owner->started_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                @else
                    <div>
                        <span class="text-sm text-gray-500">Propietario:</span>
                        <p class="text-gray-400">Sin propietario asignado</p>
                    </div>
                @endif

                @if($tenant && $tenant->user)
                    <div>
                        <span class="text-sm text-gray-500">Inquilino:</span>
                        <div class="mt-1">
                            <p class="font-medium">{{ $tenant->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $tenant->user->email }}</p>
                            @if($tenant->is_responsible)
                                <flux:badge color="blue" size="sm" class="mt-1">Responsable del Pago</flux:badge>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">Desde: {{ $tenant->started_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                @else
                    <div>
                        <span class="text-sm text-gray-500">Inquilino:</span>
                        <p class="text-gray-400">Sin inquilino asignado</p>
                    </div>
                @endif

                <div class="pt-2 border-t">
                    <flux:button href="{{ route('admin.unit-users.create') }}?unit_id={{ $unit->id }}" variant="ghost" size="sm">
                        Gestionar Asignaciones
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <!-- Residentes -->
    <div class="mt-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">Residentes ({{ $residents->count() }})</flux:heading>
                <flux:button href="{{ route('admin.residents.create') }}?unit_id={{ $unit->id }}" variant="ghost" size="sm">
                    Agregar Residentes
                </flux:button>
            </div>
        </div>
        <div class="p-4">
            @if($residents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-2">Nombre</th>
                                <th class="text-left p-2">Documento</th>
                                <th class="text-left p-2">Fecha de Nacimiento</th>
                                <th class="text-left p-2">Edad</th>
                                <th class="text-left p-2">Relación</th>
                                <th class="text-left p-2">Responsable</th>
                                <th class="text-center p-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($residents as $resident)
                                <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                                    <td class="p-2 font-medium">{{ $resident->name }}</td>
                                    <td class="p-2">
                                        {{ $resident->document_type }}: {{ $resident->document_number }}
                                    </td>
                                    <td class="p-2">
                                        {{ $resident->birth_date?->format('d/m/Y') ?? 'N/A' }}
                                    </td>
                                    <td class="p-2">
                                        @if($resident->birth_date)
                                            {{ $resident->age }} años
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="p-2">{{ $resident->relationship }}</td>
                                    <td class="p-2">
                                        @if($resident->user)
                                            {{ $resident->user->name }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-2">
                                        <div class="flex gap-2 justify-center">
                                            <flux:button href="{{ route('admin.residents.edit', $resident) }}" variant="ghost" size="sm">
                                                Editar
                                            </flux:button>
                                            <flux:button 
                                                wire:click="deleteResident({{ $resident->id }})" 
                                                wire:confirm="¿Está seguro de eliminar este residente? Esta acción no se puede deshacer."
                                                variant="ghost" 
                                                size="sm"
                                                color="red">
                                                Eliminar
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No hay residentes registrados para esta unidad funcional.</p>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <flux:button href="{{ route('admin.units.index') }}" variant="ghost">
            Volver a la Lista
        </flux:button>
    </div>
</div>
