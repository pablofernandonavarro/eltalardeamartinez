<div>
    <div class="mb-6">
        <flux:heading size="xl">Bañeros y Turnos</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Gestiona los turnos de los bañeros en las piletas
        </p>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    {{-- Estadísticas --}}
    <div class="grid gap-4 md:grid-cols-4 mb-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bañeros</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_baneros'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                    <flux:icon.users class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Turnos Activos</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['active_shifts'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                    <flux:icon.clock class="size-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Turnos Hoy</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['shifts_today'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center">
                    <flux:icon.calendar class="size-6 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Duración Promedio</p>
                    <p class="text-3xl font-bold mt-2">
                        @if($stats['avg_shift_duration'])
                            {{ number_format($stats['avg_shift_duration'] / 60, 1) }}h
                        @else
                            N/A
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Últimos 30 días</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                    <flux:icon.chart-bar class="size-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-4 flex items-center gap-2">
        <flux:button 
            wire:click="$set('filter', 'all')" 
            :variant="$filter === 'all' ? 'primary' : 'outline'"
            size="sm"
        >
            Todos
        </flux:button>
        <flux:button 
            wire:click="$set('filter', 'active')" 
            :variant="$filter === 'active' ? 'primary' : 'outline'"
            size="sm"
        >
            Activos
        </flux:button>
        <flux:button 
            wire:click="$set('filter', 'history')" 
            :variant="$filter === 'history' ? 'primary' : 'outline'"
            size="sm"
        >
            Historial
        </flux:button>
    </div>

    {{-- Tabla de turnos --}}
    <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <table class="w-full">
            <thead>
                <tr class="border-b bg-zinc-50 dark:bg-zinc-800">
                    <th class="text-left p-3">Bañero</th>
                    <th class="text-left p-3">Pileta</th>
                    <th class="text-left p-3">Inicio</th>
                    <th class="text-left p-3">Fin</th>
                    <th class="text-left p-3">Duración</th>
                    <th class="text-center p-3">Estado</th>
                    <th class="text-right p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                @if($shift->user->profilePhotoUrl())
                                    <img src="{{ $shift->user->profilePhotoUrl() }}" alt="{{ $shift->user->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold">
                                        {{ $shift->user->initials() }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold">{{ $shift->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $shift->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="font-medium">{{ $shift->pool->name }}</span>
                        </td>
                        <td class="p-3">
                            <div class="text-sm">
                                <div class="font-medium">{{ $shift->started_at->format('d/m/Y') }}</div>
                                <div class="text-gray-500">{{ $shift->started_at->format('H:i') }} hs</div>
                            </div>
                        </td>
                        <td class="p-3">
                            @if($shift->ended_at)
                                <div class="text-sm">
                                    <div class="font-medium">{{ $shift->ended_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-500">{{ $shift->ended_at->format('H:i') }} hs</div>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($shift->ended_at)
                                @php
                                    $duration = $shift->started_at->diffInMinutes($shift->ended_at);
                                    $hours = floor($duration / 60);
                                    $minutes = $duration % 60;
                                @endphp
                                <span class="text-sm">{{ $hours }}h {{ $minutes }}m</span>
                            @else
                                <flux:badge color="green">En turno</flux:badge>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($shift->isActive())
                                <flux:badge color="green">Activo</flux:badge>
                            @else
                                <flux:badge color="zinc">Finalizado</flux:badge>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            @if($shift->isActive())
                                <flux:button 
                                    wire:click="endShift({{ $shift->id }})"
                                    wire:confirm="¿Finalizar este turno?"
                                    size="sm"
                                    variant="danger"
                                    icon="x-mark"
                                >
                                    Finalizar
                                </flux:button>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            @if($filter === 'active')
                                No hay turnos activos en este momento.
                            @elseif($filter === 'history')
                                No hay historial de turnos.
                            @else
                                No hay turnos registrados.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $shifts->links() }}
    </div>

    {{-- Lista de bañeros sin turno activo --}}
    <div class="mt-8">
        <flux:heading size="lg" class="mb-4">Bañeros Disponibles</flux:heading>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($baneros as $banero)
                @php
                    $activeShift = \App\Models\PoolShift::getActiveShiftForUser($banero->id);
                @endphp
                @if(!$activeShift)
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 bg-white dark:bg-zinc-900">
                        <div class="flex items-center gap-3">
                            @if($banero->profilePhotoUrl())
                                <img src="{{ $banero->profilePhotoUrl() }}" alt="{{ $banero->name }}" class="h-12 w-12 rounded-full object-cover">
                            @else
                                <div class="h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-semibold">
                                    {{ $banero->initials() }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="font-semibold">{{ $banero->name }}</div>
                                <div class="text-xs text-gray-500">{{ $banero->email }}</div>
                            </div>
                            <flux:badge color="zinc">Disponible</flux:badge>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
