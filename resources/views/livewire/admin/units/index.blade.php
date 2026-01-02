<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Unidades Funcionales</flux:heading>
        <flux:button href="{{ route('admin.units.create') }}" variant="primary">
            Nueva Unidad Funcional
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

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <flux:field>
            <flux:label>Complejo</flux:label>
            <flux:select wire:model.live="complexId" placeholder="Todos los complejos">
                <option value="">Todos los complejos</option>
                @foreach($complexes as $complex)
                    <option value="{{ $complex->id }}">{{ $complex->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Edificio</flux:label>
            <flux:select wire:model.live="buildingId" placeholder="Todos los edificios">
                <option value="">Todos los edificios</option>
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por número o piso..." />
        </flux:field>

        <flux:field>
            <flux:label>Propietario</flux:label>
            <flux:select wire:model.live="ownerId" placeholder="Todos los propietarios">
                <option value="">Todos los propietarios</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Inquilino</flux:label>
            <flux:select wire:model.live="tenantId" placeholder="Todos los inquilinos">
                <option value="">Todos los inquilinos</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar filtros</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Unidad Funcional</th>
                    <th class="text-left p-2">Propietario</th>
                    <th class="text-left p-2">Inquilino</th>
                    <th class="text-center p-2">Residentes</th>
                    <th class="text-center p-2">Estado</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    @php
                        $owner = $unit->currentUsers->firstWhere('is_owner', true);
                        $tenant = $unit->currentUsers->firstWhere('is_owner', false);
                        // Contar residentes activos cargados (con ended_at NULL o en el futuro)
                        $residentsCount = $unit->relationLoaded('residents') 
                            ? $unit->residents->count() 
                            : \App\Models\Resident::where('unit_id', $unit->id)
                                ->where(function ($q) {
                                    $q->whereNull('ended_at')
                                      ->orWhere('ended_at', '>', now());
                                })
                                ->whereNull('deleted_at')
                                ->count();
                    @endphp
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">
                            <div class="flex flex-col">
                                <a href="{{ route('admin.units.show', $unit) }}" class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $unit->full_identifier }}
                                </a>
                                @if($unit->building && $unit->building->complex)
                                    <span class="text-sm text-gray-500">{{ $unit->building->complex->name }}</span>
                                @endif
                                @if($unit->floor)
                                    <span class="text-sm text-gray-400">Piso: {{ $unit->floor }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-2">
                            @if($owner && $owner->user)
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $owner->user->name }}</span>
                                    <span class="text-sm text-gray-500">{{ $owner->user->email }}</span>
                                    @if($owner->is_responsible)
                                        <flux:badge color="blue" size="sm" class="mt-1 w-fit">Responsable Pago</flux:badge>
                                    @endif
                                </div>
                            @elseif($unit->owner)
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-600">{{ $unit->owner }}</span>
                                    <span class="text-xs text-gray-400">(Dato del Excel - No registrado)</span>
                                </div>
                            @else
                                <span class="text-gray-400">Sin propietario</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($tenant && $tenant->user)
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $tenant->user->name }}</span>
                                    <span class="text-sm text-gray-500">{{ $tenant->user->email }}</span>
                                    @if($tenant->is_responsible)
                                        <flux:badge color="blue" size="sm" class="mt-1 w-fit">Responsable Pago</flux:badge>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">Sin inquilino</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($residentsCount > 0)
                                <flux:badge color="purple">{{ $residentsCount }} residente(s)</flux:badge>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                @if($owner && $tenant)
                                    <flux:badge color="green">Propietario + Inquilino</flux:badge>
                                @elseif($owner)
                                    <flux:badge color="purple">Solo Propietario</flux:badge>
                                @elseif($tenant)
                                    <flux:badge color="blue">Solo Inquilino</flux:badge>
                                @else
                                    <flux:badge color="gray">Sin asignación</flux:badge>
                                @endif
                            </div>
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center">
                                <flux:button href="{{ route('admin.units.show', $unit) }}" variant="ghost" size="sm">
                                    Ver
                                </flux:button>
                                <flux:button href="{{ route('admin.units.edit', $unit) }}" variant="ghost" size="sm">
                                    Editar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            No se encontraron unidades funcionales
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $units->links() }}
    </div>
</div>
