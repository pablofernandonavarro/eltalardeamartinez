<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Reservas del SUM</h2>
                <div class="flex gap-2">
                    @if($status || $dateFrom || $dateTo || $search)
                        <button wire:click="clearFilters"
                            class="rounded-lg border-2 border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Limpiar Filtros
                        </button>
                    @endif
                    <a href="{{ route('admin.sum.settings') }}"
                        class="rounded-lg border-2 border-blue-600 bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 hover:border-blue-700 dark:border-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600 dark:hover:border-blue-600"
                        wire:navigate>
                        <svg class="inline-block h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Configuración
                    </a>
                </div>
            </div>

            {{-- Flash messages --}}
            @if (session()->has('message'))
                <div class="mb-4 rounded-lg border border-green-600 bg-green-50 p-4 text-green-900 dark:border-green-500 dark:bg-green-900/30 dark:text-green-100">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">{{ session('message') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 rounded-lg border border-red-600 bg-red-50 p-4 text-red-900 dark:border-red-500 dark:bg-red-900/30 dark:text-red-100">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- Filtros --}}
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
                    <select wire:model.live="status" 
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobada</option>
                        <option value="rejected">Rechazada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha Desde</label>
                    <input type="date" wire:model.live="dateFrom" 
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha Hasta</label>
                    <input type="date" wire:model.live="dateTo" 
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Buscar</label>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                        placeholder="Nombre o Unidad" 
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Tabla de Reservas --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse ($reservations as $reservation)
                            <tr class="hover:bg-zinc-200 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">#{{ $reservation->id }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $reservation->user->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $reservation->user->email }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $reservation->unit->building->name ?? 'UF' }} - {{ $reservation->unit->number }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $reservation->date->format('d/m/Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $reservation->time_range }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $reservation->total_hours }} hrs
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-900 dark:text-white">
                                    ${{ number_format($reservation->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'border border-yellow-600 bg-yellow-200 text-yellow-900 dark:border-yellow-500 dark:bg-yellow-900/30 dark:text-yellow-100',
                                            'approved' => 'border border-green-600 bg-green-200 text-green-900 dark:border-green-500 dark:bg-green-900/30 dark:text-green-100',
                                            'rejected' => 'border border-red-600 bg-red-200 text-red-900 dark:border-red-500 dark:bg-red-900/30 dark:text-red-100',
                                            'cancelled' => 'border border-zinc-500 bg-zinc-200 text-zinc-900 dark:border-zinc-500 dark:bg-zinc-700 dark:text-zinc-200',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold leading-5 {{ $statusClasses[$reservation->status] ?? '' }}">
                                        {{ $reservation->status_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <button wire:click="viewDetails({{ $reservation->id }})" 
                                        class="mr-3 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        Ver
                                    </button>
                                    @if($reservation->status === 'pending')
                                        <button wire:click="approveReservation({{ $reservation->id }})" 
                                            class="mr-3 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                            wire:confirm="¿Está seguro que desea aprobar esta reserva?">
                                            Aprobar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2">No hay reservas registradas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($reservations->hasPages())
                <div class="mt-6">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Details Modal --}}
    @if ($showDetailsModal && $selectedReservation)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4" wire:click.self="closeDetailsModal">
            <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Detalles de Reserva #{{ $selectedReservation->id }}</h3>
                    <button wire:click="closeDetailsModal" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Status Badge --}}
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado:</span>
                        @php
                            $statusClasses = [
                                'pending' => 'border border-yellow-600 bg-yellow-200 text-yellow-900 dark:border-yellow-500 dark:bg-yellow-900/30 dark:text-yellow-100',
                                'approved' => 'border border-green-600 bg-green-200 text-green-900 dark:border-green-500 dark:bg-green-900/30 dark:text-green-100',
                                'rejected' => 'border border-red-600 bg-red-200 text-red-900 dark:border-red-500 dark:bg-red-900/30 dark:text-red-100',
                                'cancelled' => 'border border-zinc-500 bg-zinc-200 text-zinc-900 dark:border-zinc-500 dark:bg-zinc-700 dark:text-zinc-200',
                            ];
                        @endphp
                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClasses[$selectedReservation->status] ?? '' }}">
                            {{ $selectedReservation->status_label }}
                        </span>
                    </div>

                    {{-- Resident Info --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Residente</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $selectedReservation->user->name }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedReservation->user->email }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Unidad</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                {{ $selectedReservation->unit->building->name ?? 'UF' }} - {{ $selectedReservation->unit->number }}
                            </p>
                        </div>
                    </div>

                    {{-- Date & Time --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Fecha</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $selectedReservation->date->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Horario</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $selectedReservation->time_range }}</p>
                        </div>
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Duración</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $selectedReservation->total_hours }} hrs</p>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Precio por hora</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">${{ number_format($selectedReservation->price_per_hour, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-span-2 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                            <p class="text-xs text-green-600 dark:text-green-400">Total a pagar</p>
                            <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-300">${{ number_format($selectedReservation->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if($selectedReservation->notes)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Notas</p>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $selectedReservation->notes }}</p>
                        </div>
                    @endif

                    {{-- Approval/Rejection Info --}}
                    @if($selectedReservation->status === 'approved' && $selectedReservation->approved_at)
                        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                <strong>Aprobada por:</strong> {{ $selectedReservation->approvedBy->name ?? 'Sistema' }}<br>
                                <strong>Fecha:</strong> {{ $selectedReservation->approved_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif

                    @if($selectedReservation->status === 'rejected' && $selectedReservation->rejected_at)
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700 dark:bg-red-900/20">
                            <p class="text-sm text-red-800 dark:text-red-200">
                                <strong>Rechazada por:</strong> {{ $selectedReservation->rejectedBy->name ?? 'Sistema' }}<br>
                                <strong>Fecha:</strong> {{ $selectedReservation->rejected_at->format('d/m/Y H:i') }}<br>
                                @if($selectedReservation->rejection_reason)
                                    <strong>Motivo:</strong> {{ $selectedReservation->rejection_reason }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($selectedReservation->status === 'cancelled' && $selectedReservation->cancelled_at)
                        <div class="rounded-lg border border-zinc-300 bg-zinc-100 p-4 dark:border-zinc-600 dark:bg-zinc-800">
                            <p class="text-sm text-zinc-800 dark:text-zinc-200">
                                <strong>Cancelada por:</strong> {{ $selectedReservation->cancelledBy->name ?? 'Usuario' }}<br>
                                <strong>Fecha:</strong> {{ $selectedReservation->cancelled_at->format('d/m/Y H:i') }}<br>
                                @if($selectedReservation->cancellation_reason)
                                    <strong>Motivo:</strong> {{ $selectedReservation->cancellation_reason }}
                                @endif
                            </p>
                        </div>
                    @endif

                    {{-- Rejection Form (if pending) --}}
                    @if($selectedReservation->status === 'pending')
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700 dark:bg-red-900/20">
                            <label class="mb-2 block text-sm font-medium text-red-800 dark:text-red-200">
                                Motivo de rechazo (requerido para rechazar)
                            </label>
                            <textarea wire:model="rejectionReason" rows="2"
                                class="w-full rounded-lg border-red-300 text-sm dark:border-red-600 dark:bg-red-950/50 dark:text-white"
                                placeholder="Indique el motivo del rechazo..."></textarea>
                            @error('rejectionReason')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="closeDetailsModal"
                        class="flex-1 rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Cerrar
                    </button>
                    @if($selectedReservation->status === 'pending')
                        <button type="button" wire:click="approveReservation({{ $selectedReservation->id }})"
                            wire:confirm="¿Está seguro que desea aprobar esta reserva?"
                            class="flex-1 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                            Aprobar
                        </button>
                        <button type="button" wire:click="rejectReservation"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                            Rechazar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
