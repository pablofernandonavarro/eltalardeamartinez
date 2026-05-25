<div>
    <div class="mb-6">
        <flux:heading size="xl">Dashboard</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Vista general del complejo
        </p>
    </div>

    {{-- Estadísticas principales --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 transition-shadow hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Unidades</p>
                    <p class="text-3xl font-bold mt-2 text-zinc-900 dark:text-white">{{ $stats['total_units'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                    <flux:icon.home class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
            <div class="mt-3 border-t border-zinc-100 dark:border-zinc-800 pt-2">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $stats['occupied_units'] }} ocupadas</p>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 transition-shadow hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Residentes Activos</p>
                    <p class="text-3xl font-bold mt-2 text-zinc-900 dark:text-white">{{ $stats['total_residents'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                    <flux:icon.user-group class="size-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
            <div class="mt-3 border-t border-zinc-100 dark:border-zinc-800 pt-2">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">En {{ $stats['total_buildings'] }} edificios</p>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 transition-shadow hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">En Pileta Ahora</p>
                    <p class="text-3xl font-bold mt-2 text-zinc-900 dark:text-white">{{ $poolActivity['currently_inside'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-cyan-100 dark:bg-cyan-900/20 flex items-center justify-center">
                    <flux:icon.user class="size-6 text-cyan-600 dark:text-cyan-400" />
                </div>
            </div>
            <div class="mt-3 border-t border-zinc-100 dark:border-zinc-800 pt-2">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $poolActivity['entries_today'] }} ingresos hoy</p>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 transition-shadow hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Usuarios Registrados</p>
                    <p class="text-3xl font-bold mt-2 text-zinc-900 dark:text-white">{{ $stats['total_users'] }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                    <flux:icon.users class="size-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
            <div class="mt-3 border-t border-zinc-100 dark:border-zinc-800 pt-2">
                @if($pendingPayments > 0)
                    <p class="text-xs font-medium text-orange-500 flex items-center gap-1">
                        <flux:icon.exclamation-circle class="size-3.5" />
                        {{ $pendingPayments }} pagos pendientes
                    </p>
                @else
                    <p class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                        <flux:icon.check-circle class="size-3.5" />
                        Sistema activo
                    </p>
                @endif
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
                <div class="py-8 text-center">
                    <flux:icon.chart-bar class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                    <p class="text-zinc-500 dark:text-zinc-400">No hay actividad registrada en los últimos 7 días</p>
                </div>
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
                <div class="py-8 text-center">
                    <flux:icon.newspaper class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                    <p class="text-zinc-500 dark:text-zinc-400">No hay noticias publicadas</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Accesos Rápidos --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
        <flux:heading size="lg" class="mb-4">Accesos Rápidos</flux:heading>
        
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <flux:button href="{{ route('admin.pools.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.beaker class="size-5" />
                Gestión de Piletas
            </flux:button>
            <flux:button href="{{ route('admin.residents.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.user-group class="size-5" />
                Residentes
            </flux:button>
            <flux:button href="{{ route('admin.units.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.building-office class="size-5" />
                Unidades
            </flux:button>
            <flux:button href="{{ route('admin.news.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.newspaper class="size-5" />
                Noticias
            </flux:button>
            <flux:button href="{{ route('admin.expenses.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.currency-dollar class="size-5" />
                Expensas
            </flux:button>
            <flux:button href="{{ route('admin.buildings.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.building-office-2 class="size-5" />
                Edificios
            </flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.users class="size-5" />
                Usuarios
            </flux:button>
            <flux:button href="{{ route('admin.rules.index') }}" variant="outline" class="justify-start" wire:navigate>
                <flux:icon.document-text class="size-5" />
                Reglamentos
            </flux:button>
        </div>
    </div>
</div>
