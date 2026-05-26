<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="xl">Reservas del SUM</flux:heading>
                    <p class="text-sm text-zinc-500 mt-1">Gestioná y aprobá las reservas del Salón de Usos Múltiples.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($status || $dateFrom || $dateTo || $search)
                        <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark">Limpiar Filtros</flux:button>
                    @endif
                    <flux:button href="{{ route('admin.sum.reservations.create') }}" variant="filled" icon="plus" wire:navigate>
                        Nueva reserva
                    </flux:button>
                    <flux:button href="{{ route('admin.sum.settings') }}" variant="primary" icon="cog-6-tooth" wire:navigate>
                        Configuración
                    </flux:button>
                </div>
            </div>

            {{-- Flash messages --}}
            @if (session()->has('message'))
                <flux:callout color="green" icon="check-circle" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            @if (session()->has('error'))
                <flux:callout color="red" icon="exclamation-circle" class="mb-4">
                    {{ session('error') }}
                </flux:callout>
            @endif

            {{-- Filtros --}}
            <div class="mb-6 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <flux:field>
                        <flux:label>Estado</flux:label>
                        <flux:select wire:model.live="status">
                            <option value="">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="approved">Aprobada</option>
                            <option value="rejected">Rechazada</option>
                            <option value="cancelled">Cancelada</option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha Desde</flux:label>
                        <flux:input type="date" wire:model.live="dateFrom" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha Hasta</flux:label>
                        <flux:input type="date" wire:model.live="dateTo" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Buscar</flux:label>
                        <flux:input wire:model.live.debounce.300ms="search" placeholder="Nombre o Unidad" icon="magnifying-glass" />
                    </flux:field>
                </div>
            </div>

            {{-- Tabla de Reservas --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Residente</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horario</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horas</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                            @forelse ($reservations as $reservation)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">#{{ $reservation->id }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $reservation->user->name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $reservation->user->email }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                        {{ $reservation->unit->building->name ?? 'UF' }} - {{ $reservation->unit->number }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                        {{ $reservation->date->format('d/m/Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $reservation->time_range }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $reservation->total_hours }} hrs
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-mono font-semibold text-zinc-900 dark:text-white">
                                        ${{ number_format($reservation->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @php
                                            $statusBadgeColor = [
                                                'pending' => 'yellow',
                                                'approved' => 'green',
                                                'rejected' => 'red',
                                                'cancelled' => 'zinc',
                                            ][$reservation->status->value] ?? 'zinc';
                                        @endphp
                                        <flux:badge color="{{ $statusBadgeColor }}">{{ $reservation->status_label }}</flux:badge>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <div class="flex justify-center gap-2">
                                            <flux:button wire:click="viewDetails({{ $reservation->id }})" variant="ghost" size="sm" icon="eye">
                                                Ver
                                            </flux:button>
                                            @if($reservation->status->value === 'pending')
                                                <flux:button wire:click="approveReservation({{ $reservation->id }})"
                                                    variant="ghost" size="sm" color="green"
                                                    wire:confirm="¿Está seguro que desea aprobar esta reserva?">
                                                    Aprobar
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center">
                                        <flux:icon.calendar-days class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                        <p class="text-zinc-500 dark:text-zinc-400">No hay reservas registradas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                        <flux:icon.x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Status Badge --}}
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado:</span>
                        @php
                            $statusBadgeColor = [
                                'pending' => 'yellow',
                                'approved' => 'green',
                                'rejected' => 'red',
                                'cancelled' => 'zinc',
                            ][$selectedReservation->status->value] ?? 'zinc';
                        @endphp
                        <flux:badge color="{{ $statusBadgeColor }}">{{ $selectedReservation->status_label }}</flux:badge>
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
                    @if($selectedReservation->status->value === 'approved' && $selectedReservation->approved_at)
                        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                <strong>Aprobada por:</strong> {{ $selectedReservation->approvedBy->name ?? 'Sistema' }}<br>
                                <strong>Fecha:</strong> {{ $selectedReservation->approved_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif

                    @if($selectedReservation->status->value === 'rejected' && $selectedReservation->rejected_at)
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

                    @if($selectedReservation->status->value === 'cancelled' && $selectedReservation->cancelled_at)
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
                    @if($selectedReservation->status->value === 'pending')
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
                    <flux:button type="button" wire:click="closeDetailsModal" variant="ghost" class="flex-1">
                        Cerrar
                    </flux:button>
                    @if($selectedReservation->status->value === 'pending')
                        <flux:button type="button" wire:click="approveReservation({{ $selectedReservation->id }})"
                            wire:confirm="¿Está seguro que desea aprobar esta reserva?"
                            variant="primary" class="flex-1">
                            Aprobar
                        </flux:button>
                        <flux:button type="button" wire:click="rejectReservation"
                            variant="danger" class="flex-1">
                            Rechazar
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
