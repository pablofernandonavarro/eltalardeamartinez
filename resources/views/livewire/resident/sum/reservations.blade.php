<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Reservar SUM</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Salon de Usos Multiples</p>
            </div>

            {{-- Unit selector inline --}}
            <div class="flex items-center gap-3">
                @if ($unitUsers->count() > 1)
                    <select wire:model.live="unitId"
                        class="rounded-lg border-zinc-300 bg-white text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        @foreach ($unitUsers as $unitUser)
                            <option value="{{ $unitUser->unit_id }}">
                                {{ $unitUser->unit->building->name ?? 'Edificio' }} - {{ $unitUser->unit->number }}
                            </option>
                        @endforeach
                    </select>
                @elseif ($unitUsers->count() === 1)
                    <span class="rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-900 dark:bg-zinc-800 dark:text-white">
                        {{ $unitUsers->first()->unit->building->name ?? 'Edificio' }} - {{ $unitUsers->first()->unit->number }}
                    </span>
                @endif

                @if ($unitId)
                    @if ($isResponsible)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900/50 dark:text-green-200">
                            <svg class="mr-1.5 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Responsable
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                            <svg class="mr-1.5 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Solo lectura
                        </span>
                    @endif
                @endif
            </div>
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

        {{-- Info panel for non-responsible --}}
        @if ($unitId && !$isResponsible)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
                <p class="text-sm text-amber-700 dark:text-amber-300">
                    <strong>Nota:</strong> Solo el responsable de pago puede realizar reservas del SUM. Puede ver la disponibilidad pero no crear reservas.
                </p>
            </div>
        @endif

        {{-- Main Calendar Container --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            {{-- Calendar Header --}}
            <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                <button wire:click="previousMonth" class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Anterior</span>
                </button>

                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                    {{ $monthName }} {{ $currentYear }}
                </h2>

                <button wire:click="nextMonth" class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700">
                    <span class="hidden sm:inline">Siguiente</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Calendar Grid --}}
            <div class="p-2 sm:p-4">
                {{-- Day Headers --}}
                <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700">
                    @foreach (['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'] as $dayName)
                        <div class="py-3 text-center text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar Days --}}
                <div class="grid grid-cols-7">
                    @foreach ($calendarDays as $index => $day)
                        @php
                            $isFirstRow = $index < 7;
                            $isLastInRow = ($index + 1) % 7 === 0;
                        @endphp
                        <div
                            wire:click="selectDate('{{ $day['date'] }}')"
                            @class([
                                'relative min-h-[80px] sm:min-h-[100px] lg:min-h-[120px] border-b border-r border-zinc-100 dark:border-zinc-800 p-1 sm:p-2 transition-colors cursor-pointer',
                                'border-r-0' => $isLastInRow,
                                'hover:bg-blue-50 dark:hover:bg-blue-900/20' => $day['isSelectable'],
                                'bg-zinc-50 dark:bg-zinc-800/30' => !$day['isCurrentMonth'],
                                'cursor-not-allowed' => !$day['isSelectable'],
                                'bg-blue-50 dark:bg-blue-900/30 ring-2 ring-inset ring-blue-500' => $selectedDate === $day['date'],
                            ])
                        >
                            {{-- Day Number --}}
                            <div class="flex items-start justify-between">
                                <span @class([
                                    'flex h-6 w-6 sm:h-7 sm:w-7 items-center justify-center rounded-full text-xs sm:text-sm font-medium',
                                    'text-zinc-400 dark:text-zinc-600' => !$day['isCurrentMonth'],
                                    'text-zinc-900 dark:text-white' => $day['isCurrentMonth'] && !$day['isToday'],
                                    'bg-blue-600 text-white' => $day['isToday'],
                                ])>
                                    {{ $day['day'] }}
                                </span>

                                @if ($day['isPast'])
                                    <span class="text-[10px] text-zinc-400 dark:text-zinc-600">pasado</span>
                                @elseif ($day['isTooFar'])
                                    <span class="text-[10px] text-zinc-400 dark:text-zinc-600">+{{ $maxDaysAdvance }}d</span>
                                @endif
                            </div>

                            {{-- Reservations indicator --}}
                            @if ($day['reservationCount'] > 0)
                                <div class="mt-1">
                                    <div class="flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 dark:bg-amber-900/50">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        <span class="text-[10px] font-medium text-amber-700 dark:text-amber-300">
                                            {{ $day['reservationCount'] }} {{ $day['reservationCount'] === 1 ? 'reserva' : 'reservas' }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Selected Date Panel --}}
            @if ($selectedDate)
                <div class="border-t border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                            </h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                @if ($selectedDateReservations->isEmpty())
                                    Sin reservas - Disponible todo el dia
                                @else
                                    {{ $selectedDateReservations->count() }} {{ $selectedDateReservations->count() === 1 ? 'reserva' : 'reservas' }} para este dia
                                @endif
                            </p>
                        </div>

                        @if ($isResponsible)
                            @php
                                $selectedDateObj = \Carbon\Carbon::parse($selectedDate);
                                $canReserve = !$selectedDateObj->isPast() && $selectedDateObj->lte(now()->addDays($maxDaysAdvance));
                            @endphp
                            @if ($canReserve)
                                <button wire:click="openCreateModal"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Nueva Reserva
                                </button>
                            @endif
                        @endif
                    </div>

                    {{-- Reservations List --}}
                    @if ($selectedDateReservations->isNotEmpty())
                        <div class="mt-4 space-y-2">
                            @foreach ($selectedDateReservations as $reservation)
                                <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-zinc-900 dark:text-white">
                                                {{ $reservation->time_range }}
                                            </p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $reservation->unit->building->name ?? '' }} - {{ $reservation->unit->number }}
                                                @if ($reservation->user_id === auth()->id())
                                                    <span class="ml-1 text-blue-600 dark:text-blue-400">(tu reserva)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
                                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
                                                'cancelled' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200',
                                                'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
                                            ];
                                        @endphp
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$reservation->status] ?? $statusColors['pending'] }}">
                                            {{ $reservation->status_label }}
                                        </span>

                                        @if ($reservation->user_id === auth()->id() && in_array($reservation->status, ['pending', 'approved']))
                                            <button wire:click="openCancelModal({{ $reservation->id }})"
                                                class="rounded p-1 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Bottom Info Cards --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Price Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/50">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Precio por hora</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-white">${{ number_format($pricePerHour, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Hours Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Horario disponible</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-white">{{ $openTime }} - {{ $closeTime }}</p>
                    </div>
                </div>
            </div>

            {{-- Advance Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/50">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Anticipacion maxima</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-white">{{ $maxDaysAdvance }} dias</p>
                    </div>
                </div>
            </div>

            {{-- Notice Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Aviso minimo</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-white">{{ $minHoursNotice }} horas</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- My Upcoming Reservations --}}
        @if ($isResponsible && $myUpcomingReservations->isNotEmpty())
            <div class="mt-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Mis proximas reservas</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($myUpcomingReservations as $reservation)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $reservation->date->format('d/m/Y') }}
                                </p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $reservation->time_range }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
                                        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$reservation->status] ?? '' }}">
                                    {{ $reservation->status_label }}
                                </span>
                                @if (in_array($reservation->status, ['pending', 'approved']))
                                    <button wire:click="openCancelModal({{ $reservation->id }})"
                                        class="rounded p-1 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Create Reservation Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4" wire:click.self="closeCreateModal">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Nueva Reserva</h3>
                    <button wire:click="closeCreateModal" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="createReservation">
                    <div class="space-y-5">
                        {{-- Date Display --}}
                        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                            <p class="text-sm text-blue-600 dark:text-blue-400">Fecha seleccionada</p>
                            <p class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                                {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                            </p>
                        </div>

                        {{-- Time Selection --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hora inicio</label>
                                <select wire:model.live="startTime"
                                    class="w-full rounded-lg border-zinc-300 bg-white text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="">Seleccionar</option>
                                    @foreach ($availableTimeSlots as $slot)
                                        <option value="{{ $slot }}">{{ $slot }}</option>
                                    @endforeach
                                </select>
                                @error('startTime')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hora fin</label>
                                <select wire:model.live="endTime"
                                    class="w-full rounded-lg border-zinc-300 bg-white text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                                    <option value="">Seleccionar</option>
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
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notas (opcional)</label>
                            <textarea wire:model="notes" rows="2"
                                class="w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                placeholder="Motivo, cantidad de personas, etc."></textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Price Summary --}}
                        @if ($calculatedHours > 0)
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="mb-2 flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">Duracion</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $calculatedHours }} hora(s)</span>
                                </div>
                                <div class="mb-2 flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">Precio x hora</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">${{ number_format($pricePerHour, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                                    <span class="text-base font-semibold text-zinc-900 dark:text-white">Total</span>
                                    <span class="text-xl font-bold text-green-600 dark:text-green-400">${{ number_format($calculatedAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($requiresApproval)
                            <div class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                <strong>Nota:</strong> La reserva quedara pendiente de aprobacion por el administrador.
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" wire:click="closeCreateModal"
                            class="flex-1 rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Cancel Reservation Modal --}}
    @if ($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4" wire:click.self="closeCancelModal">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Cancelar Reserva</h3>
                    <button wire:click="closeCancelModal" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="cancelReservation">
                    <div class="space-y-4">
                        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                            <p class="text-sm text-red-800 dark:text-red-200">
                                Esta seguro que desea cancelar esta reserva? Esta accion no se puede deshacer.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Motivo de cancelacion (opcional)
                            </label>
                            <textarea wire:model="cancellationReason" rows="2"
                                class="w-full rounded-lg border-zinc-300 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                placeholder="Indique el motivo..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button" wire:click="closeCancelModal"
                            class="flex-1 rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            Volver
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                            Cancelar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
