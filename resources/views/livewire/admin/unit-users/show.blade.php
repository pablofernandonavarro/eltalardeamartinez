<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Detalle de Asignación Usuario-Unidad</flux:heading>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.unit-users.edit', $unitUser) }}" variant="ghost">
                Editar
            </flux:button>
            <flux:button href="{{ route('admin.unit-users.index') }}" variant="ghost">
                Volver
            </flux:button>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Información de la Relación -->
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">Información de la Asignación</flux:heading>
            
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <flux:text size="sm" class="text-gray-500">Usuario</flux:text>
                    <div class="mt-1">
                        <span class="font-medium">{{ $unitUser->user->name }}</span>
                        <div class="text-sm text-gray-500">{{ $unitUser->user->email }}</div>
                        <flux:badge color="blue" class="mt-1">{{ $unitUser->user->role?->label() ?? 'Sin rol' }}</flux:badge>
                    </div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">Unidad Funcional</flux:text>
                    <div class="mt-1">
                        <span class="font-medium">{{ $unitUser->unit->full_identifier }}</span>
                        @if($unitUser->unit->building && $unitUser->unit->building->complex)
                            <div class="text-sm text-gray-500">{{ $unitUser->unit->building->complex->name }}</div>
                        @endif
                    </div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">Es Propietario</flux:text>
                    <div class="mt-1">
                        @if($unitUser->is_owner ?? false)
                            <flux:badge color="purple">Sí</flux:badge>
                        @else
                            <span class="text-gray-400">No</span>
                        @endif
                    </div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">Es Responsable del Pago</flux:text>
                    <div class="mt-1">
                        @if($unitUser->is_responsible)
                            <flux:badge color="green">Sí</flux:badge>
                        @else
                            <span class="text-gray-400">No</span>
                        @endif
                    </div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">Fecha de Inicio</flux:text>
                    <div class="mt-1 font-medium">{{ $unitUser->started_at->format('d/m/Y') }}</div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">Fecha de Fin</flux:text>
                    <div class="mt-1">
                        @if($unitUser->ended_at)
                            <span class="font-medium">{{ $unitUser->ended_at->format('d/m/Y') }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">Estado</flux:text>
                    <div class="mt-1">
                        @if($unitUser->ended_at)
                            <flux:badge color="gray">Inactiva</flux:badge>
                        @else
                            <flux:badge color="green">Activa</flux:badge>
                        @endif
                    </div>
                </div>

                @if($unitUser->notes)
                    <div class="md:col-span-2">
                        <flux:text size="sm" class="text-gray-500">Notas</flux:text>
                        <div class="mt-1 text-sm">{{ $unitUser->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Propietario de la Unidad -->
        @if($owner)
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Propietario de la Unidad</flux:heading>
                
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:text size="sm" class="text-gray-500">Nombre</flux:text>
                        <div class="mt-1 font-medium">{{ $owner->user->name }}</div>
                    </div>

                    <div>
                        <flux:text size="sm" class="text-gray-500">Email</flux:text>
                        <div class="mt-1 text-sm">{{ $owner->user->email }}</div>
                    </div>

                    <div>
                        <flux:text size="sm" class="text-gray-500">Fecha de Inicio</flux:text>
                        <div class="mt-1 text-sm">{{ $owner->started_at->format('d/m/Y') }}</div>
                    </div>

                    <div>
                        <flux:text size="sm" class="text-gray-500">Estado</flux:text>
                        <div class="mt-1">
                            @if($owner->ended_at)
                                <flux:badge color="gray">Inactivo</flux:badge>
                            @else
                                <flux:badge color="green">Activo</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Responsable del Pago -->
        @if($responsible && $responsible->id !== $owner?->id)
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Responsable del Pago</flux:heading>
                
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:text size="sm" class="text-gray-500">Nombre</flux:text>
                        <div class="mt-1 font-medium">{{ $responsible->user->name }}</div>
                    </div>

                    <div>
                        <flux:text size="sm" class="text-gray-500">Email</flux:text>
                        <div class="mt-1 text-sm">{{ $responsible->user->email }}</div>
                    </div>

                    <div>
                        <flux:text size="sm" class="text-gray-500">Fecha de Inicio</flux:text>
                        <div class="mt-1 text-sm">{{ $responsible->started_at->format('d/m/Y') }}</div>
                    </div>

                    <div>
                        <flux:text size="sm" class="text-gray-500">Estado</flux:text>
                        <div class="mt-1">
                            @if($responsible->ended_at)
                                <flux:badge color="gray">Inactivo</flux:badge>
                            @else
                                <flux:badge color="green">Activo</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Usuarios Activos de la Unidad -->
        @if($activeUsers->count() > 0)
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Usuarios Activos de la Unidad</flux:heading>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-2">Usuario</th>
                                <th class="text-center p-2">Propietario</th>
                                <th class="text-center p-2">Responsable Pago</th>
                                <th class="text-left p-2">Fecha Inicio</th>
                                <th class="text-left p-2">Fecha Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeUsers as $activeUser)
                                <tr class="border-b hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <td class="p-2">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ $activeUser->user->name }}</span>
                                            <span class="text-sm text-gray-500">{{ $activeUser->user->email }}</span>
                                            <flux:badge color="blue" size="sm" class="mt-1 w-fit">{{ $activeUser->user->role?->label() ?? 'Sin rol' }}</flux:badge>
                                        </div>
                                    </td>
                                    <td class="p-2 text-center">
                                        @if($activeUser->is_owner ?? false)
                                            <flux:badge color="purple">Sí</flux:badge>
                                        @else
                                            <span class="text-gray-400">No</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-center">
                                        @if($activeUser->is_responsible)
                                            <flux:badge color="green">Sí</flux:badge>
                                        @else
                                            <span class="text-gray-400">No</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-sm">{{ $activeUser->started_at->format('d/m/Y') }}</td>
                                    <td class="p-2 text-sm">
                                        @if($activeUser->ended_at)
                                            {{ $activeUser->ended_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Residentes de la Unidad -->
        @if($residents->count() > 0)
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Residentes de la Unidad</flux:heading>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-2">Nombre</th>
                                <th class="text-left p-2">Documento</th>
                                <th class="text-left p-2">Responsable</th>
                                <th class="text-left p-2">Relación</th>
                                <th class="text-left p-2">Edad</th>
                                <th class="text-left p-2">Fecha Inicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($residents as $resident)
                                <tr class="border-b hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <td class="p-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ $resident->name }}</span>
                                            @if($resident->isMinor())
                                                <flux:badge color="yellow" size="sm">Menor</flux:badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2 text-sm">
                                        @if($resident->document_type && $resident->document_number)
                                            {{ $resident->document_type }}: {{ $resident->document_number }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-sm">
                                        @if($resident->user)
                                            <div class="flex flex-col">
                                                <span>{{ $resident->user->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $resident->user->email }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-sm">{{ $resident->relationship ?? '-' }}</td>
                                    <td class="p-2 text-sm">
                                        @if($resident->birth_date)
                                            {{ $resident->age }} años
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-sm">
                                        @if($resident->started_at)
                                            {{ $resident->started_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">Residentes de la Unidad</flux:heading>
                <p class="text-gray-500">No hay residentes registrados para esta unidad.</p>
            </div>
        @endif
    </div>
</div>
