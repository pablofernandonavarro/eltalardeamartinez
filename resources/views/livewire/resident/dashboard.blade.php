<div>
    <div class="mb-6">
        <flux:heading size="xl">Bienvenido, {{ auth()->user()->name }}</flux:heading>
        <p class="text-sm text-gray-500 mt-1">Rol: {{ auth()->user()->role?->label() ?? 'Sin rol asignado' }}</p>
    </div>

    <!-- Mis Unidades Funcionales -->
    @if($units->count() > 0)
        <div class="mb-6">
            <flux:heading size="lg" class="mb-4">Mis Unidades Funcionales</flux:heading>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($units as $unitUser)
                    <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-lg">{{ $unitUser->unit->full_identifier }}</h3>
                            @if($unitUser->is_owner ?? false)
                                <flux:badge color="purple">Propietario</flux:badge>
                            @endif
                            @if($unitUser->is_responsible)
                                <flux:badge color="blue">Responsable Pago</flux:badge>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">{{ $unitUser->unit->building->complex->name }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Resumen de Expensas -->
    <div class="grid gap-6 md:grid-cols-2 mb-6">
        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Expensas Pendientes</h3>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($pendingExpenses, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Mis Unidades</h3>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $units->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Expensas -->
    @if($expenseDetails->count() > 0)
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Últimas Expensas</flux:heading>
                <flux:button href="{{ route('resident.expenses.index') }}" variant="ghost" size="sm">
                    Ver todas
                </flux:button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">Unidad</th>
                            <th class="text-left p-2">Concepto</th>
                            <th class="text-right p-2">Monto</th>
                            <th class="text-right p-2">Pagado</th>
                            <th class="text-center p-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseDetails as $detail)
                            <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                                <td class="p-2">{{ $detail->unit->full_identifier }}</td>
                                <td class="p-2">{{ $detail->expense->concept->name }}</td>
                                <td class="p-2 text-right">${{ number_format($detail->amount, 2, ',', '.') }}</td>
                                <td class="p-2 text-right">${{ number_format($detail->paid_amount, 2, ',', '.') }}</td>
                                <td class="p-2 text-center">
                                    @if($detail->status === \App\ExpenseStatus::Pagada)
                                        <flux:badge color="green">{{ $detail->status->label() }}</flux:badge>
                                    @elseif($detail->status === \App\ExpenseStatus::Vencida)
                                        <flux:badge color="red">{{ $detail->status->label() }}</flux:badge>
                                    @else
                                        <flux:badge color="yellow">{{ $detail->status->label() }}</flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Últimos Accesos a Piletas -->
    @if($poolEntries->count() > 0)
        <div>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Últimos Accesos a Piletas</flux:heading>
                <flux:button href="{{ route('resident.pools.index') }}" variant="ghost" size="sm">
                    Ver todos
                </flux:button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">Pileta</th>
                            <th class="text-left p-2">Unidad</th>
                            <th class="text-left p-2">Fecha y Hora</th>
                            <th class="text-center p-2">Invitados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($poolEntries as $entry)
                            <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                                <td class="p-2 font-medium">{{ $entry->pool->name }}</td>
                                <td class="p-2">{{ $entry->unit->full_identifier }}</td>
                                <td class="p-2">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                                <td class="p-2 text-center">{{ $entry->guests_count ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($units->isEmpty())
        <flux:callout color="blue">
            No tienes unidades funcionales asignadas. Contacta al administrador para que te asigne una unidad.
        </flux:callout>
    @endif
</div>
