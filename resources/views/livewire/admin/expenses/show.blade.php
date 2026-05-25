<div>
    {{-- Encabezado --}}
    <div class="mb-6 flex flex-wrap gap-4 items-start justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <flux:button href="{{ route('admin.expenses.index') }}" variant="ghost" size="sm" icon="arrow-left">
                    Expensas
                </flux:button>
                <span class="text-zinc-400">/</span>
                <span class="text-sm text-zinc-500">{{ $expense->building->name }}</span>
            </div>
            <flux:heading size="xl">{{ $expense->description }}</flux:heading>
            <div class="flex flex-wrap gap-3 mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                <span class="flex items-center gap-1">
                    <flux:icon.building-office class="size-4" />
                    {{ $expense->building->complex->name }} — {{ $expense->building->name }}
                </span>
                <span class="flex items-center gap-1">
                    <flux:icon.calendar class="size-4" />
                    Período {{ $expense->period }}
                </span>
                <span class="flex items-center gap-1">
                    <flux:icon.clock class="size-4" />
                    Vence {{ $expense->due_date->format('d/m/Y') }}
                </span>
                <flux:badge color="{{ $expense->type === \App\ExpenseType::Ordinaria ? 'blue' : 'purple' }}" size="sm">
                    {{ $expense->type->label() }}
                </flux:badge>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Unidades</p>
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Monto Total</p>
            <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100 font-mono">${{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Pagado</p>
            <p class="text-lg font-bold text-green-600 dark:text-green-400 font-mono">${{ number_format($stats['total_paid'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Pendiente</p>
            <p class="text-lg font-bold text-red-600 dark:text-red-400 font-mono">${{ number_format($stats['total_pending'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">Estado</p>
            <div class="space-y-1 text-xs">
                <div class="flex justify-between"><span class="text-yellow-600">Pendiente</span><span class="font-semibold">{{ $stats['pendiente'] }}</span></div>
                <div class="flex justify-between"><span class="text-blue-600">Parcial</span><span class="font-semibold">{{ $stats['parcial'] }}</span></div>
                <div class="flex justify-between"><span class="text-green-600">Pagada</span><span class="font-semibold">{{ $stats['pagada'] }}</span></div>
                <div class="flex justify-between"><span class="text-red-600">Vencida</span><span class="font-semibold">{{ $stats['vencida'] }}</span></div>
            </div>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Cobranza</p>
            @php
                $pct = $stats['total_amount'] > 0 ? round($stats['total_paid'] / $stats['total_amount'] * 100, 1) : 0;
            @endphp
            <p class="text-2xl font-bold {{ $pct >= 80 ? 'text-green-600' : ($pct >= 40 ? 'text-yellow-600' : 'text-red-600') }}">{{ $pct }}%</p>
            <div class="mt-2 h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-700">
                <div class="h-1.5 rounded-full {{ $pct >= 80 ? 'bg-green-500' : ($pct >= 40 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-4">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[220px] flex-1">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search"
                    placeholder="UF, depto o propietario..."
                    icon="magnifying-glass" />
            </flux:field>

            <flux:field class="min-w-[180px]">
                <flux:label>Estado</flux:label>
                <flux:select wire:model.live="status" placeholder="Todos">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="parcial">Parcial</option>
                    <option value="pagada">Pagada</option>
                    <option value="vencida">Vencida</option>
                </flux:select>
            </flux:field>

            <div class="flex items-end">
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark">Limpiar</flux:button>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 cursor-pointer select-none whitespace-nowrap"
                            wire:click="sort('uf_code')">
                            UF
                            @if($sortBy === 'uf_code')
                                <flux:icon.{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }} class="inline size-3 ml-1" />
                            @endif
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 cursor-pointer select-none whitespace-nowrap"
                            wire:click="sort('unit_number')">
                            Depto
                            @if($sortBy === 'unit_number')
                                <flux:icon.{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }} class="inline size-3 ml-1" />
                            @endif
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 cursor-pointer select-none"
                            wire:click="sort('owner')">
                            Propietario
                            @if($sortBy === 'owner')
                                <flux:icon.{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }} class="inline size-3 ml-1" />
                            @endif
                        </th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 cursor-pointer select-none whitespace-nowrap"
                            wire:click="sort('amount')">
                            Monto
                            @if($sortBy === 'amount')
                                <flux:icon.{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }} class="inline size-3 ml-1" />
                            @endif
                        </th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 cursor-pointer select-none whitespace-nowrap"
                            wire:click="sort('paid_amount')">
                            Pagado
                            @if($sortBy === 'paid_amount')
                                <flux:icon.{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }} class="inline size-3 ml-1" />
                            @endif
                        </th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            Pendiente
                        </th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 cursor-pointer select-none"
                            wire:click="sort('status')">
                            Estado
                            @if($sortBy === 'status')
                                <flux:icon.{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }} class="inline size-3 ml-1" />
                            @endif
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($details as $detail)
                        @php
                            $pending = (float) $detail->amount - (float) $detail->paid_amount;
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $detail->unit?->uf_code ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                {{ $detail->unit?->number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                {{ $detail->unit?->owner ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                ${{ number_format((float) $detail->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono whitespace-nowrap {{ (float) $detail->paid_amount > 0 ? 'text-green-600 dark:text-green-400' : 'text-zinc-400' }}">
                                ${{ number_format((float) $detail->paid_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono whitespace-nowrap {{ $pending > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-400' }}">
                                ${{ number_format($pending, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <flux:badge color="{{ $detail->status->color() }}" size="sm">
                                    {{ $detail->status->label() }}
                                </flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <flux:icon.magnifying-glass class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron detalles</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($details->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-800 font-semibold">
                            <td colspan="3" class="px-4 py-3 text-xs text-zinc-500 uppercase tracking-wider">
                                {{ $details->total() }} unidades (página {{ $details->currentPage() }}/{{ $details->lastPage() }})
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                ${{ number_format($details->sum('amount'), 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-green-600 dark:text-green-400 whitespace-nowrap">
                                ${{ number_format($details->sum('paid_amount'), 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-red-600 dark:text-red-400 whitespace-nowrap">
                                ${{ number_format($details->sum(fn($d) => (float)$d->amount - (float)$d->paid_amount), 2, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $details->links() }}
    </div>
</div>
