<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
        @if (session()->has('message'))
            <div class="mb-4 rounded-lg border border-green-600 bg-green-50 p-4 text-green-900 dark:border-green-500 dark:bg-green-900/30 dark:text-green-100">
                <div class="flex items-center">
                    <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Pagos y Facturas del SUM</h2>
                <div class="flex flex-wrap gap-3">
                    <button wire:click="exportToExcel" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700">
                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Exportar a Excel
                    </button>
                    <button wire:click="clearFilters" class="rounded-lg border-2 border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Mes Actual</div>
                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">${{ number_format($stats['totalMonth'], 2) }}</div>
                </div>
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <div class="text-sm font-medium text-green-600 dark:text-green-400">Pagos Confirmados</div>
                    <div class="text-2xl font-bold text-green-900 dark:text-green-100">${{ number_format($stats['totalPaid'], 2) }}</div>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <div class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pagos Pendientes</div>
                    <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">${{ number_format($stats['totalPending'], 2) }}</div>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-900/20">
                    <div class="text-sm font-medium text-purple-600 dark:text-purple-400">Total Reservas</div>
                    <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $stats['totalReservations'] }}</div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-5">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado Pago</label>
                    <select wire:model.live="paymentStatus" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="paid">Pagado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Mes</label>
                    <select wire:model.live="month" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Año</label>
                    <select wire:model.live="year" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Método de Pago</label>
                    <select wire:model.live="paymentMethod" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="card">Tarjeta</option>
                        <option value="online">Pago Online</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Buscar</label>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nombre o Unidad" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Tabla de Pagos -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Reserva</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">#{{ $payment->id }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">{{ $payment->reservation->user->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $payment->reservation->unit->building->name ?? '' }} - {{ $payment->reservation->unit->number }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $payment->reservation->date->format('d/m/Y') }}<br>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $payment->reservation->start_time }} - {{ $payment->reservation->end_time }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($payment->reservation->total_hours, 1) }} hrs</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-900 dark:text-white">${{ number_format($payment->amount, 2) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">{{ $payment->payment_method_label }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($payment->status === 'paid')
                                        <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold leading-5 text-green-800 dark:bg-green-900/50 dark:text-green-200">
                                            {{ $payment->status_label }}
                                        </span>
                                    @elseif ($payment->status === 'pending')
                                        <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold leading-5 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200">
                                            {{ $payment->status_label }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold leading-5 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                                            {{ $payment->status_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    @if ($payment->status === 'pending')
                                        <button wire:click="openPaymentModal({{ $payment->id }})" class="mr-3 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Confirmar Pago</button>
                                    @endif
                                    <button wire:click="downloadInvoice({{ $payment->id }})" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Descargar PDF
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                    No hay pagos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-6">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Pago -->
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 text-xl font-bold text-zinc-900 dark:text-white">Confirmar Pago</h3>

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Método de Pago *</label>
                    <select wire:model="modalPaymentMethod" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione un método</option>
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="card">Tarjeta</option>
                        <option value="online">Pago Online</option>
                    </select>
                    @error('modalPaymentMethod')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Referencia de Transacción</label>
                    <input wire:model="transactionReference" type="text" placeholder="Ej: TRF-12345" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                    @error('transactionReference')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notas</label>
                    <textarea wire:model="notes" rows="3" placeholder="Notas adicionales sobre el pago..." class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="closePaymentModal" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Cancelar
                    </button>
                    <button wire:click="confirmPayment" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700">
                        Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
