<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Residentes</flux:heading>
        <flux:button href="{{ route('admin.residents.create') }}" variant="primary">
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

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Unidad Funcional</flux:label>
            <flux:select wire:model.live="unitId" placeholder="Todas las unidades">
                <option value="">Todas las unidades</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Estado</flux:label>
            <flux:select wire:model.live="status" placeholder="Todos los estados">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Nombre o documento..." />
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
                    <th class="text-left p-2">Unidad Funcional</th>
                    <th class="text-left p-2">Documento</th>
                    <th class="text-left p-2">Responsable</th>
                    <th class="text-left p-2">Relación</th>
                    <th class="text-left p-2">Edad</th>
                    <th class="text-left p-2">Fecha Inicio</th>
                    <th class="text-left p-2">Fecha Fin</th>
                    <th class="text-center p-2">Estado</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($residents as $resident)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $resident->name }}</span>
                                @if($resident->isMinor())
                                    <flux:badge color="yellow" size="sm" class="mt-1">Menor</flux:badge>
                                @endif
                            </div>
                        </td>
                        <td class="p-2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $resident->unit->full_identifier }}</span>
                                @if($resident->unit->building && $resident->unit->building->complex)
                                    <span class="text-sm text-gray-500">{{ $resident->unit->building->complex->name }}</span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-2">
                            @if($resident->document_type && $resident->document_number)
                                <span class="text-sm">{{ $resident->document_type }}: {{ $resident->document_number }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($resident->user)
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium">{{ $resident->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $resident->user->email }}</span>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2">
                            <span class="text-sm">{{ $resident->relationship ?? '-' }}</span>
                        </td>
                        <td class="p-2">
                            @if($resident->birth_date)
                                <span class="text-sm">{{ $resident->age }} años</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($resident->started_at)
                                {{ $resident->started_at->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($resident->ended_at)
                                {{ $resident->ended_at->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($resident->ended_at)
                                <flux:badge color="gray">Inactivo</flux:badge>
                            @else
                                <flux:badge color="green">Activo</flux:badge>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                <flux:button href="{{ route('admin.residents.edit', $resident) }}" variant="ghost" size="sm">
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
                        <td colspan="10" class="p-8 text-center text-gray-500">
                            No se encontraron residentes
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $residents->links() }}
    </div>
</div>
