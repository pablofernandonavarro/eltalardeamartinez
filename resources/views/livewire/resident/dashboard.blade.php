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
                    <flux:icon.currency-dollar class="w-6 h-6 text-white" />
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
                    <flux:icon.building-office class="w-6 h-6 text-white" />
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
                <flux:button href="{{ route('resident.expenses.index') }}" variant="ghost" size="sm" icon="arrow-right">
                    Ver todas
                </flux:button>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Concepto</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Monto</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pagado</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($expenseDetails as $detail)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                    <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $detail->unit->full_identifier }}</td>
                                    <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $detail->expense->concept->name }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-zinc-900 dark:text-zinc-100">${{ number_format($detail->amount, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-green-600 dark:text-green-400">${{ number_format($detail->paid_amount, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
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
        </div>
    @endif

    <!-- Últimos Accesos a Piletas -->
    @if($poolEntries->count() > 0)
        <div>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Últimos Accesos a Piletas</flux:heading>
                <flux:button href="{{ route('resident.pools.day-pass') }}" variant="ghost" size="sm">
                    Mi QR (hoy)
                </flux:button>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pileta</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha y Hora</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invitados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($poolEntries as $entry)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $entry->pool->name }}</td>
                                    <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $entry->unit->full_identifier }}</td>
                                    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-center text-zinc-900 dark:text-zinc-100">{{ $entry->guests_count ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($units->isEmpty())
        <flux:callout color="blue">
            No tienes unidades funcionales asignadas. Contacta al administrador para que te asigne una unidad.
        </flux:callout>
    @endif
</div>
