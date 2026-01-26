<div class="p-6">
    <div class="mx-auto max-w-7xl">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Reservar SUM</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Salon de Usos Multiples</p>
        </div>

        {{-- Flash messages --}}
        @if (session()->has('message'))
            <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-800 dark:bg-green-900/50 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            {{-- Left Column: Unit selector + My reservations --}}
            <div class="space-y-6">
                {{-- Unit Selector --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Unidad Funcional
                    </label>
                    @if ($unitUsers->count() > 1)
                        <select wire:model.live="unitId"
                            class="w-full rounded-md border-zinc-300 bg-white text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                            @foreach ($unitUsers as $unitUser)
                                <option value="{{ $unitUser->unit_id }}">
                                    {{ $unitUser->unit->building->name ?? 'Edificio' }} -
                                    {{ $unitUser->unit->number }}
                                </option>
                            @endforeach
                        </select>
                    @elseif ($unitUsers->count() === 1)
                        <p class="text-sm text-zinc-900 dark:text-white">
                            {{ $unitUsers->first()->unit->building->name ?? 'Edificio' }} -
                            {{ $unitUsers->first()->unit->number }}
                        </p>
                    @else
                        <p class="text-sm text-zinc-500">No tiene unidades asignadas</p>
                    @endif

                    {{-- Responsible indicator --}}
                    @if ($unitId)
                        <div class="mt-3">
                            @if ($isResponsible)
                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/50 dark:text-green-200">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Responsable de pago
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    No es responsable de pago
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Info panel for non-responsible --}}
                @if ($unitId && !$isResponsible)
                    <div
                        class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
                        <h3 class="mb-2 font-medium text-amber-800 dark:text-amber-200">Informacion</h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            Solo el responsable de pago de la unidad funcional puede realizar reservas del SUM.
                            Contacte al administrador si necesita modificar esta configuracion.
                        </p>
                    </div>
                @endif

                {{-- My Upcoming Reservations --}}
                @if ($isResponsible && $myUpcomingReservations->isNotEmpty())
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Mis proximas reservas</h3>
                        <div class="space-y-2">
                            @foreach ($myUpcomingReservations as $reservation)
                                <div
                                    class="flex items-center justify-between rounded border border-zinc-100 p-2 dark:border-zinc-800">
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $reservation->date->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ $reservation->time_range }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded px-2 py-0.5 text-xs font-medium bg-{{ $reservation->status_color }}-100 text-{{ $reservation->status_color }}-800 dark:bg-{{ $reservation->status_color }}-900/50 dark:text-{{ $reservation->status_color }}-200">
                                            {{ $reservation->status_label }}
                                        </span>
                                        @if (in_array($reservation->status, ['pending', 'approved']))
                                            <button wire:click="openCancelModal({{ $reservation->id }})"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Settings info --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="mb-3 font-medium text-zinc-900 dark:text-white">Informacion</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Precio por hora:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">${{ number_format($pricePerHour, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Horario:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $openTime }} - {{ $closeTime }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Anticipacion max:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $maxDaysAdvance }} dias</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">Aviso minimo:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $minHoursNotice }} horas</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Center/Right: Calendar + Day detail --}}
            <div class="lg:col-span-3">
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    {{-- Calendar header --}}
                    <div class="flex items-center justify-between border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <button wire:click="previousMonth"
                            class="rounded p-1 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $monthName }} {{ $currentYear }}
                        </h2>
                        <button wire:click="nextMonth"
                            class="rounded p-1 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    {{-- Calendar grid --}}
                    <div class="p-4">
                        {{-- Day headers --}}
                        <div class="mb-2 grid grid-cols-7 gap-1">
                            @foreach (['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'] as $dayName)
                                <div class="py-2 text-center text-xs font-medium text-zinc-500">
                                    {{ $dayName }}
                                </div>
                            @endforeach
                        </div>

                        {{-- Days --}}
                        <div class="grid grid-cols-7 gap-1">
                            @foreach ($calendarDays as $day)
                                <button
                                    wire:click="selectDate('{{ $day['date'] }}')"
                                    @class([
                                        'relative rounded-lg p-2 text-center transition-colors min-h-[60px]',
                                        'hover:bg-zinc-100 dark:hover:bg-zinc-800' => $day['isSelectable'],
                                        'cursor-not-allowed opacity-50' => !$day['isSelectable'],
                                        'bg-blue-100 dark:bg-blue-900/50' => $selectedDate === $day['date'],
                                        'ring-2 ring-blue-500' => $day['isToday'],
                                        'text-zinc-400 dark:text-zinc-600' => !$day['isCurrentMonth'],
                                        'text-zinc-900 dark:text-white' => $day['isCurrentMonth'],
                                    ])
                                    @if(!$day['isSelectable']) disabled @endif
                                >
                                    <span class="text-sm">{{ $day['day'] }}</span>
                                    @if ($day['reservationCount'] > 0)
                                        <div class="mt-1 flex justify-center">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Selected date detail --}}
                    @if ($selectedDate)
                        <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="font-medium text-zinc-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                                </h3>
                                @if ($isResponsible)
                                    <button wire:click="openCreateModal"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                        Nueva Reserva
                                    </button>
                                @endif
                            </div>

                            @if ($selectedDateReservations->isEmpty())
                                <p class="text-sm text-zinc-500">No hay reservas para este dia.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($selectedDateReservations as $reservation)
                                        <div
                                            class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                            <div>
                                                <p class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $reservation->time_range }}
                                                </p>
                                                <p class="text-sm text-zinc-500">
                                                    {{ $reservation->unit->building->name ?? '' }} -
                                                    {{ $reservation->unit->number }}
                                                </p>
                                            </div>
                                            <span
                                                class="rounded px-2 py-1 text-xs font-medium bg-{{ $reservation->status_color }}-100 text-{{ $reservation->status_color }}-800 dark:bg-{{ $reservation->status_color }}-900/50 dark:text-{{ $reservation->status_color }}-200">
                                                {{ $reservation->status_label }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Create Reservation Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeCreateModal">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Nueva Reserva</h3>
                    <button wire:click="closeCreateModal" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="createReservation">
                    <div class="space-y-4">
                        {{-- Date --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha</label>
                            <p class="mt-1 text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                            </p>
                        </div>

                        {{-- Start Time --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hora de
                                inicio</label>
                            <select wire:model.live="startTime"
                                class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach ($availableTimeSlots as $slot)
                                    <option value="{{ $slot }}">{{ $slot }}</option>
                                @endforeach
                            </select>
                            @error('startTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- End Time --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hora de
                                fin</label>
                            <select wire:model.live="endTime"
                                class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach ($availableTimeSlots as $slot)
                                    @if ($slot > $startTime)
                                        <option value="{{ $slot }}">{{ $slot }}</option>
                                    @endif
                                @endforeach
                                @if ($closeTime > $startTime)
                                    <option value="{{ $closeTime }}">{{ $closeTime }}</option>
                                @endif
                            </select>
                            @error('endTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notas
                                (opcional)</label>
                            <textarea wire:model="notes" rows="2"
                                class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                placeholder="Motivo de la reserva, cantidad de personas, etc."></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Price calculation --}}
                        @if ($calculatedHours > 0)
                            <div class="rounded-lg bg-zinc-100 p-3 dark:bg-zinc-800">
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">Duracion:</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $calculatedHours }}
                                        hora(s)</span>
                                </div>
                                <div class="mt-1 flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">Precio por hora:</span>
                                    <span
                                        class="font-medium text-zinc-900 dark:text-white">${{ number_format($pricePerHour, 0, ',', '.') }}</span>
                                </div>
                                <div class="mt-2 flex justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                                    <span class="font-medium text-zinc-900 dark:text-white">Total:</span>
                                    <span
                                        class="text-lg font-bold text-green-600 dark:text-green-400">${{ number_format($calculatedAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($requiresApproval)
                            <div class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                La reserva quedara pendiente de aprobacion por el administrador.
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" wire:click="closeCreateModal"
                            class="flex-1 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Cancel Reservation Modal --}}
    @if ($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeCancelModal">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Cancelar Reserva</h3>
                    <button wire:click="closeCancelModal" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="cancelReservation">
                    <div class="space-y-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Esta seguro que desea cancelar esta reserva? Esta accion no se puede deshacer.
                        </p>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Motivo de cancelacion (opcional)
                            </label>
                            <textarea wire:model="cancellationReason" rows="2"
                                class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                placeholder="Indique el motivo..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" wire:click="closeCancelModal"
                            class="flex-1 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            Volver
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Cancelar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
