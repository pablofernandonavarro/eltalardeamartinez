<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Unidades Funcionales</flux:heading>
        <flux:button href="{{ route('admin.unit-users.create') }}" variant="primary">
            Nueva Asignación
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
            <flux:label>Usuario</flux:label>
            <flux:select wire:model.live="userId" placeholder="Todos los usuarios">
                <option value="">Todos los usuarios</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </flux:select>
        </flux:field>

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
                <option value="active">Activas</option>
                <option value="inactive">Inactivas</option>
            </flux:select>
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Usuario</th>
                    <th class="text-left p-2">Unidad Funcional</th>
                    <th class="text-center p-2">Propietario</th>
                    <th class="text-center p-2">Responsable Pago</th>
                    <th class="text-left p-2">Fecha Inicio</th>
                    <th class="text-left p-2">Fecha Fin</th>
                    <th class="text-center p-2">Estado</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unitUsers as $unitUser)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $unitUser->user->name }}</span>
                                <span class="text-sm text-gray-500">{{ $unitUser->user->email }}</span>
                            </div>
                        </td>
                        <td class="p-2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $unitUser->unit->full_identifier }}</span>
                                @if($unitUser->unit->building && $unitUser->unit->building->complex)
                                    <span class="text-sm text-gray-500">{{ $unitUser->unit->building->complex->name }}</span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-2 text-center">
                            @if($unitUser->is_owner ?? false)
                                <flux:badge color="purple">Sí</flux:badge>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($unitUser->is_responsible)
                                <flux:badge color="blue">Sí</flux:badge>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </td>
                        <td class="p-2">{{ $unitUser->started_at->format('d/m/Y') }}</td>
                        <td class="p-2">
                            @if($unitUser->ended_at)
                                {{ $unitUser->ended_at->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($unitUser->ended_at)
                                <flux:badge color="gray">Inactiva</flux:badge>
                            @else
                                <flux:badge color="green">Activa</flux:badge>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                <flux:button href="{{ route('admin.unit-users.show', $unitUser) }}" variant="ghost" size="sm">
                                    Ver
                                </flux:button>
                                <flux:button href="{{ route('admin.unit-users.edit', $unitUser) }}" variant="ghost" size="sm">
                                    Editar
                                </flux:button>
                                <flux:button wire:click="delete({{ $unitUser->id }})" 
                                    wire:confirm="¿Está seguro de eliminar esta asignación? Esta acción no se puede deshacer."
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
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            No se encontraron asignaciones
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $unitUsers->links() }}
    </div>
</div>
