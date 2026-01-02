<div>
    <div class="mb-6">
        <flux:heading size="xl">Dashboard</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Vista general del complejo
        </p>
    </div>

    {{-- Estadísticas principales --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Unidades</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_units'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['occupied_units'] }} ocupadas</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                    <flux:icon.home class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Residentes Activos</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_residents'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">En {{ $stats['total_buildings'] }} edificios</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                    <flux:icon.user-group class="size-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En Pileta Ahora</p>
                    <p class="text-3xl font-bold mt-2">{{ $poolActivity['currently_inside'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $poolActivity['entries_today'] }} ingresos hoy</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-cyan-100 dark:bg-cyan-900/20 flex items-center justify-center">
                    <flux:icon.user class="size-6 text-cyan-600 dark:text-cyan-400" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios Registrados</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_users'] }}</p>
                    @if($pendingPayments > 0)
                        <p class="text-xs text-orange-500 mt-1">{{ $pendingPayments }} pagos pendientes</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Sistema activo</p>
                    @endif
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                    <flux:icon.users class="size-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mb-6">
        {{-- Actividad de Pileta --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Actividad de Pileta (7 días)</flux:heading>
                <flux:badge color="cyan">{{ $poolActivity['total_people_today'] }} personas hoy</flux:badge>
            </div>
            
            @if($poolEntriesLastWeek->count() > 0)
                <div class="space-y-3">
                    @php
                        $maxCount = $poolEntriesLastWeek->max('count');
                    @endphp
                    @foreach($poolEntriesLastWeek as $entry)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($entry->date)->format('d/m') }} - {{ \Carbon\Carbon::parse($entry->date)->isoFormat('ddd') }}
                                </span>
                                <span class="font-semibold">{{ $entry->count }} ingresos</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-zinc-700 rounded-full h-2">
                                <div class="bg-cyan-600 h-2 rounded-full transition-all" style="width: {{ $maxCount > 0 ? ($entry->count / $maxCount) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No hay actividad registrada en los últimos 7 días</p>
            @endif
        </div>

        {{-- Noticias Recientes --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Noticias Recientes</flux:heading>
                <flux:button href="{{ route('admin.news.index') }}" variant="ghost" size="sm" wire:navigate>
                    Ver todas
                </flux:button>
            </div>
            
            @if($recentNews->count() > 0)
                <div class="space-y-4">
                    @foreach($recentNews as $news)
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <a href="{{ route('admin.news.edit', $news) }}" class="font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                                {{ $news->title }}
                            </a>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ \Illuminate\Support\Str::limit(strip_tags($news->content), 100) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                {{ $news->published_at?->diffForHumans() ?? 'Sin publicar' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No hay noticias publicadas</p>
            @endif
        </div>
    </div>

    {{-- Accesos Rápidos --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
        <flux:heading size="lg" class="mb-4">Accesos Rápidos</flux:heading>
        
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <flux:button href="{{ route('admin.pools.index') }}" variant="outline" class="justify-start" icon="water" wire:navigate>
                Gestión de Piletas
            </flux:button>
            <flux:button href="{{ route('admin.residents.index') }}" variant="outline" class="justify-start" icon="user-group" wire:navigate>
                Residentes
            </flux:button>
            <flux:button href="{{ route('admin.units.index') }}" variant="outline" class="justify-start" icon="building-office" wire:navigate>
                Unidades
            </flux:button>
            <flux:button href="{{ route('admin.news.index') }}" variant="outline" class="justify-start" icon="newspaper" wire:navigate>
                Noticias
            </flux:button>
            <flux:button href="{{ route('admin.expenses.index') }}" variant="outline" class="justify-start" icon="currency-dollar" wire:navigate>
                Expensas
            </flux:button>
            <flux:button href="{{ route('admin.buildings.index') }}" variant="outline" class="justify-start" icon="building-office-2" wire:navigate>
                Edificios
            </flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="outline" class="justify-start" icon="users" wire:navigate>
                Usuarios
            </flux:button>
            <flux:button href="{{ route('admin.rules.index') }}" variant="outline" class="justify-start" icon="document-text" wire:navigate>
                Reglamentos
            </flux:button>
        </div>
    </div>
</div>
