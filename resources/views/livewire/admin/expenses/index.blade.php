<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Gestión de Expensas</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Resumen de expensas por edificio, con totales pagados y pendientes.</p>
        </div>
        <flux:button href="{{ route('admin.expenses.import') }}" variant="primary" icon="arrow-up-tray">
            Importar PDF
        </flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[220px] flex-1">
                <flux:label>Edificio</flux:label>
                <flux:select wire:model.live="buildingId" placeholder="Todos los edificios">
                    <option value="">Todos los edificios</option>
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}">{{ $building->complex->name }} - {{ $building->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[160px]">
                <flux:label>Tipo</flux:label>
                <flux:select wire:model.live="type" placeholder="Todos los tipos">
                    <option value="">Todos los tipos</option>
                    <option value="{{ \App\ExpenseType::Ordinaria->value }}">Ordinaria</option>
                    <option value="{{ \App\ExpenseType::Extraordinaria->value }}">Extraordinaria</option>
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[200px] flex-1">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por descripción..." icon="magnifying-glass" />
            </flux:field>

            <div class="flex items-end">
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark">Limpiar</flux:button>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Edificio</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tipo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Concepto</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vencimiento</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Monto Total</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pagado</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendiente</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $expense->building->complex->name }} - {{ $expense->building->name }}</td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $expense->type === \App\ExpenseType::Ordinaria ? 'blue' : 'purple' }}">
                                    {{ $expense->type->label() }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $expense->concept?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300 whitespace-nowrap">{{ $expense->due_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-900 dark:text-zinc-100">${{ number_format($expense->total_amount, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-green-600 dark:text-green-400">${{ number_format($expense->total_paid, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-red-600 dark:text-red-400">${{ number_format($expense->total_pending, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <flux:button href="{{ route('admin.expenses.show', $expense) }}" variant="ghost" size="sm" icon="eye">
                                    Ver
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <flux:icon.currency-dollar class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron expensas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
</div>
