<div>
    {{-- ================================================================
         MIS EXPENSAS — Vista del residente
         Muestra: resumen de cuenta, historial de cuotas, desglose de rubros
         ================================================================ --}}

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Mis Expensas</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Resumen de tu cuenta y estado de liquidaciones.</p>
        </div>

        {{-- Selector de unidad si el usuario tiene más de una --}}
        @if ($unitUsers->count() > 1)
            <div class="w-full sm:w-auto">
                <flux:select wire:model.live="selectedUnitId" label="Unidad">
                    @foreach ($unitUsers as $uu)
                        <option value="{{ $uu->unit_id }}">
                            {{ $uu->unit->building->name }} — Depto {{ $uu->unit->number }}
                        </option>
                    @endforeach
                </flux:select>
            </div>
        @endif
    </div>

    @if (! $selectedUnitId)
        <flux:callout color="blue">
            No tenés unidades funcionales asignadas. Contactá al administrador.
        </flux:callout>
    @else

    {{-- ================================================================
         RESUMEN DE CUENTA
         ================================================================ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">

        {{-- Cuota del mes --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/40">
                    <flux:icon.calendar-days class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cuota del Mes</p>
            </div>
            @if ($summary['ultimo_periodo'])
                <p class="text-xs text-zinc-400 mb-0.5">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $summary['ultimo_periodo'])->locale('es')->isoFormat('MMMM YYYY') }}
                </p>
            @endif
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                ${{ number_format($summary['ultima_cuota'], 2, ',', '.') }}
            </p>
        </div>

        {{-- Total a pagar (incluye deuda + intereses) --}}
        <div class="rounded-xl border {{ $summary['deuda_vigente'] > 0 ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900' }} p-5">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $summary['deuda_vigente'] > 0 ? 'bg-red-100 dark:bg-red-900/40' : 'bg-green-100 dark:bg-green-900/40' }}">
                    <flux:icon.currency-dollar class="h-5 w-5 {{ $summary['deuda_vigente'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" />
                </div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                    {{ $summary['deuda_vigente'] > 0 ? 'Total a Regularizar' : 'Estado de Cuenta' }}
                </p>
            </div>
            @if ($summary['deuda_vigente'] > 0)
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                    ${{ number_format($summary['ultimo_total'], 2, ',', '.') }}
                </p>
                <p class="text-xs text-red-500 dark:text-red-400 mt-0.5">
                    Incluye deuda e intereses
                </p>
            @else
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">Al día</p>
                <p class="text-xs text-green-500 dark:text-green-400 mt-0.5">Sin deuda pendiente</p>
            @endif
        </div>

        {{-- Total pagado histórico --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/40">
                    <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                </div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Pagado</p>
            </div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                ${{ number_format($summary['total_pagado'], 2, ',', '.') }}
            </p>
        </div>

        {{-- Cuotas pendientes --}}
        <div class="rounded-xl border {{ $summary['cantidad_pendientes'] > 0 ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/10' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900' }} p-5">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $summary['cantidad_pendientes'] > 0 ? 'bg-amber-100 dark:bg-amber-900/40' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                    <flux:icon.clock class="h-5 w-5 {{ $summary['cantidad_pendientes'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500 dark:text-zinc-400' }}" />
                </div>
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cuotas Pendientes</p>
            </div>
            <p class="text-2xl font-bold {{ $summary['cantidad_pendientes'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">
                {{ $summary['cantidad_pendientes'] }}
            </p>
        </div>
    </div>

    {{-- ================================================================
         ESTADO DE CUENTA — si hay deuda, mostrar el desglose del PDF
         ================================================================ --}}
    @if ($summary['deuda_vigente'] > 0)
        <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="px-5 py-4 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
                <div class="flex items-center gap-2">
                    <flux:icon.exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Estado de Cuenta — Liquidación Más Reciente</h3>
                </div>
            </div>
            @php
                // Buscar el detalle más reciente con metadata de estado de cuenta
                $ultimoDetalle = $expenseDetails->first() ?? null;
                $meta = $ultimoDetalle?->metadata ?? [];
            @endphp
            @if ($meta)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                            <th class="text-right px-4 py-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">S. Anterior</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Pagos</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Bonific.</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Deuda</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Intereses</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">Gastos A</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-red-600 uppercase tracking-wider">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-zinc-900">
                            <td class="text-right px-4 py-3 font-mono text-zinc-700 dark:text-zinc-300">
                                ${{ number_format($meta['previous_balance'] ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="text-right px-4 py-3 font-mono text-green-600 dark:text-green-400">
                                ${{ number_format($meta['payments_period'] ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="text-right px-4 py-3 font-mono text-blue-600 dark:text-blue-400">
                                ${{ number_format($meta['bonification'] ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="text-right px-4 py-3 font-mono text-red-600 dark:text-red-400">
                                ${{ number_format($meta['accumulated_debt'] ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="text-right px-4 py-3 font-mono text-red-600 dark:text-red-400">
                                ${{ number_format($meta['interests'] ?? 0, 2, ',', '.') }}
                            </td>
                            @if ($ultimoDetalle)
                            <td class="text-right px-4 py-3 font-mono text-zinc-900 dark:text-zinc-100 font-semibold">
                                ${{ number_format($ultimoDetalle->amount, 2, ',', '.') }}
                            </td>
                            @else
                            <td class="text-right px-4 py-3 font-mono text-zinc-400">—</td>
                            @endif
                            <td class="text-right px-4 py-3 font-mono text-red-700 dark:text-red-300 font-bold text-base">
                                ${{ number_format($meta['total_to_pay'] ?? $summary['ultimo_total'], 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    @endif

    {{-- ================================================================
         FILTROS
         ================================================================ --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-4">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[160px] flex-1">
                <flux:label>Estado</flux:label>
                <flux:select wire:model.live="filterStatus" placeholder="Todos">
                    <option value="">Todos</option>
                    <option value="{{ \App\ExpenseStatus::Pendiente->value }}">Pendiente</option>
                    <option value="{{ \App\ExpenseStatus::Parcial->value }}">Pago Parcial</option>
                    <option value="{{ \App\ExpenseStatus::Pagada->value }}">Pagada</option>
                    <option value="{{ \App\ExpenseStatus::Vencida->value }}">Vencida</option>
                </flux:select>
            </flux:field>

            @if (count($periods) > 0)
                <flux:field class="min-w-[180px] flex-1">
                    <flux:label>Período</flux:label>
                    <flux:select wire:model.live="filterPeriod" placeholder="Todos los períodos">
                        <option value="">Todos los períodos</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period }}">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->locale('es')->isoFormat('MMMM YYYY') }}
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @endif

            @if ($filterStatus || $filterPeriod)
                <div class="flex items-end">
                    <flux:button wire:click="$set('filterStatus', null); $set('filterPeriod', null)" variant="ghost" icon="x-mark">
                        Limpiar
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- ================================================================
         TABLA DE LIQUIDACIONES
         ================================================================ --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Período</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Vencimiento</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Cuota</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pagado</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pendiente</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Rubros</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($expenseDetails as $detail)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors">
                            {{-- Período --}}
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                @if ($detail->expense->period)
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $detail->expense->period)->locale('es')->isoFormat('MMMM YYYY') }}
                                @else
                                    {{ $detail->expense->concept?->name ?? '—' }}
                                @endif
                                @if ($detail->expense->description)
                                    <br><span class="text-xs text-zinc-400 font-normal">{{ $detail->expense->description }}</span>
                                @endif
                            </td>

                            {{-- Vencimiento --}}
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                                {{ $detail->expense->due_date->format('d/m/Y') }}
                            </td>

                            {{-- Cuota del mes --}}
                            <td class="px-4 py-3 text-right font-mono text-zinc-900 dark:text-zinc-100">
                                ${{ number_format($detail->amount, 2, ',', '.') }}
                            </td>

                            {{-- Pagado --}}
                            <td class="px-4 py-3 text-right font-mono text-green-600 dark:text-green-400">
                                ${{ number_format($detail->paid_amount, 2, ',', '.') }}
                            </td>

                            {{-- Pendiente --}}
                            <td class="px-4 py-3 text-right font-mono {{ $detail->pending_amount > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-400' }}">
                                ${{ number_format(max(0, $detail->pending_amount), 2, ',', '.') }}
                            </td>

                            {{-- Estado --}}
                            <td class="px-4 py-3 text-center">
                                <flux:badge color="{{ $detail->status->color() }}" size="sm">
                                    {{ $detail->status->label() }}
                                </flux:badge>
                            </td>

                            {{-- Acción: ver rubros --}}
                            <td class="px-4 py-3 text-center">
                                @if ($detail->metadata && isset($detail->metadata['rubros']))
                                    <flux:button
                                        wire:click="toggleRubros({{ $detail->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="{{ $expandedDetailId === $detail->id ? 'chevron-up' : 'chevron-down' }}"
                                    >
                                        Detalle
                                    </flux:button>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Fila expandible: desglose de rubros --}}
                        @if ($expandedDetailId === $detail->id && $detail->metadata && isset($detail->metadata['rubros']))
                            <tr>
                                <td colspan="7" class="px-4 py-0 bg-zinc-50 dark:bg-zinc-800/40">
                                    <div class="py-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-3">
                                            Desglose por rubro — proporción de tu unidad
                                        </p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @php
                                                $labels = [
                                                    'rubro_01_remuneraciones'       => 'Remuneraciones al Personal',
                                                    'rubro_02_servicios_publicos'    => 'Servicios Públicos',
                                                    'rubro_03_abonos_servicios'      => 'Abonos de Servicios',
                                                    'rubro_04_mantenimiento_comun'   => 'Mantenimiento Partes Comunes',
                                                    'rubro_05_reparaciones_uf'       => 'Reparaciones en Unidades',
                                                    'rubro_06_gastos_bancarios'      => 'Gastos Bancarios',
                                                    'rubro_07_gastos_limpieza'       => 'Gastos de Limpieza',
                                                    'rubro_08_gastos_administracion' => 'Gastos de Administración',
                                                    'rubro_09_seguros'               => 'Seguros',
                                                    'rubro_10_otros'                 => 'Otros',
                                                ];
                                            @endphp
                                            @foreach ($detail->metadata['rubros'] as $key => $monto)
                                                <div class="flex items-center justify-between rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 px-3 py-2">
                                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                                        {{ $labels[$key] ?? $key }}
                                                    </span>
                                                    <span class="font-mono text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                        ${{ number_format($monto, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Estado de cuenta de esta cuota --}}
                                        @php $meta = $detail->metadata; @endphp
                                        @if (($meta['previous_balance'] ?? 0) > 0 || ($meta['interests'] ?? 0) > 0)
                                            <div class="mt-4 border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Estado de cuenta</p>
                                                <div class="flex flex-wrap gap-4 text-sm">
                                                    @if (($meta['previous_balance'] ?? 0) != 0)
                                                        <div>
                                                            <span class="text-zinc-500">S. Anterior:</span>
                                                            <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100 ml-1">
                                                                ${{ number_format($meta['previous_balance'], 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if (($meta['payments_period'] ?? 0) != 0)
                                                        <div>
                                                            <span class="text-zinc-500">Pagos período:</span>
                                                            <span class="font-mono font-medium text-green-600 dark:text-green-400 ml-1">
                                                                ${{ number_format($meta['payments_period'], 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if (($meta['accumulated_debt'] ?? 0) != 0)
                                                        <div>
                                                            <span class="text-zinc-500">Deuda:</span>
                                                            <span class="font-mono font-medium text-red-600 dark:text-red-400 ml-1">
                                                                ${{ number_format($meta['accumulated_debt'], 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if (($meta['interests'] ?? 0) != 0)
                                                        <div>
                                                            <span class="text-zinc-500">Intereses:</span>
                                                            <span class="font-mono font-medium text-red-600 dark:text-red-400 ml-1">
                                                                ${{ number_format($meta['interests'], 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if (($meta['total_to_pay'] ?? 0) != 0)
                                                        <div class="font-semibold">
                                                            <span class="text-zinc-700 dark:text-zinc-300">Total a pagar:</span>
                                                            <span class="font-mono text-red-700 dark:text-red-300 ml-1">
                                                                ${{ number_format($meta['total_to_pay'], 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Historial de pagos de esta cuota --}}
                                        @if ($detail->payments->isNotEmpty())
                                            <div class="mt-4 border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Pagos registrados</p>
                                                <div class="space-y-1">
                                                    @foreach ($detail->payments as $payment)
                                                        <div class="flex items-center justify-between text-sm">
                                                            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                                                                <flux:icon.check-circle class="h-4 w-4 text-green-500" />
                                                                {{ $payment->payment_date->format('d/m/Y') }}
                                                                @if ($payment->payment_method)
                                                                    · {{ $payment->payment_method }}
                                                                @endif
                                                            </div>
                                                            <span class="font-mono font-medium text-green-600 dark:text-green-400">
                                                                ${{ number_format($payment->amount, 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <flux:icon.currency-dollar class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron expensas para los filtros seleccionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $expenseDetails->links() }}
    </div>

    @endif {{-- fin del bloque @if selectedUnitId --}}
</div>
