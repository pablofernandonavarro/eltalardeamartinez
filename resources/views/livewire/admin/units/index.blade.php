<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Unidades Funcionales</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Listado de unidades con sus propietarios, inquilinos y residentes.</p>
        </div>
        <flux:button href="{{ route('admin.units.create') }}" variant="primary" icon="plus">
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

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
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
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por número o piso..." icon="magnifying-glass" />
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
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark">Limpiar filtros</flux:button>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad Funcional</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ambientes</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Propietario</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Inquilino</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Residentes</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
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
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <a href="{{ route('admin.units.show', $unit) }}" class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $unit->full_identifier }}
                                </a>
                                @if($unit->building && $unit->building->complex)
                                    <span class="text-sm text-zinc-500">{{ $unit->building->complex->name }}</span>
                                @endif
                                @if($unit->floor_label !== '-')
                                    <span class="text-sm text-zinc-400">Piso: {{ $unit->floor_label }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($unit->rooms)
                                <flux:badge color="blue">{{ $unit->rooms }}</flux:badge>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($owner && $owner->user)
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $owner->user->name }}</span>
                                    <span class="text-sm text-zinc-500">{{ $owner->user->email }}</span>
                                    @if($owner->is_responsible)
                                        <flux:badge color="blue" size="sm" class="mt-1 w-fit">Responsable Pago</flux:badge>
                                    @endif
                                </div>
                            @elseif($unit->owner)
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $unit->owner }}</span>
                                    <span class="text-xs text-zinc-400">(Dato del Excel - No registrado)</span>
                                </div>
                            @else
                                <span class="text-zinc-400">Sin propietario</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($tenant && $tenant->user)
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $tenant->user->name }}</span>
                                    <span class="text-sm text-zinc-500">{{ $tenant->user->email }}</span>
                                    @if($tenant->is_responsible)
                                        <flux:badge color="blue" size="sm" class="mt-1 w-fit">Responsable Pago</flux:badge>
                                    @endif
                                </div>
                            @else
                                <span class="text-zinc-400">Sin inquilino</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($residentsCount > 0)
                                <flux:badge color="purple">{{ $residentsCount }} residente(s)</flux:badge>
                            @else
                                <span class="text-zinc-400">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
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
                        <td class="px-4 py-3">
                            <div class="flex gap-2 justify-center">
                                <flux:button href="{{ route('admin.units.show', $unit) }}" variant="ghost" size="sm" icon="eye">
                                    Ver
                                </flux:button>
                                <flux:button href="{{ route('admin.units.edit', $unit) }}" variant="ghost" size="sm" icon="pencil-square">
                                    Editar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <flux:icon.building-office class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                            <p class="text-zinc-500 dark:text-zinc-400">No se encontraron unidades funcionales</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $units->links() }}
    </div>
</div>
