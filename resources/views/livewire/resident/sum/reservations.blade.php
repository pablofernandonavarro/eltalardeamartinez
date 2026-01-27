<div class="p-4 lg:p-6"
     x-data="calendarApp()"
     x-init="initCalendar(); setupEventListeners()"
     wire:key="reservations-{{ $unitId }}"
     x-on:livewire:navigated.window="if (calendar) { calendar.render(); }">

    <script>
        window.sumCalendarConfig = {
            events: @json($calendarEvents),
            isResponsible: @json($isResponsible),
            openTime: '{{ $openTime }}',
            closeTime: '{{ $closeTime }}',
            maxDaysAdvance: {{ $maxDaysAdvance }}
        };
    </script>
    <div class="mx-auto max-w-7xl">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Reservar SUM</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Salon de Usos Multiples - Haz clic en un horario libre para reservar</p>
            </div>

            {{-- Unit selector inline --}}
            <div class="flex flex-wrap items-center gap-3">
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
                            Puede reservar
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

        {{-- Legend --}}
        <div class="mb-4 flex flex-wrap items-center gap-4 p-3">
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leyenda:</span>
            <div class="flex items-center gap-2">
                <span class="h-4 w-4 rounded bg-blue-500"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Mis reservas</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-4 w-4 rounded bg-amber-500"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Otras reservas (ocupado)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-dashed border-zinc-400 dark:border-zinc-600"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Disponible</span>
            </div>
        </div>

        {{-- FullCalendar Container --}}
        <div class="overflow-hidden rounded-xl border border-zinc-700">
            <div id="fullcalendar" class="min-h-[400px] p-2 sm:p-4" wire:ignore>
                <div x-show="loading" class="flex h-[400px] items-center justify-center">
                    <div class="text-center">
                        <svg class="mx-auto h-8 w-8 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Cargando calendario...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Info Cards --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-3 rounded-lg border border-zinc-700 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-900/50">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">Precio por hora</p>
                    <p class="text-lg font-bold text-white">${{ number_format($pricePerHour, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg border border-zinc-700 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-900/50">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">Horario disponible</p>
                    <p class="text-lg font-bold text-white">{{ $openTime }} - {{ $closeTime }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg border border-zinc-700 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-900/50">
                    <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">Anticipacion maxima</p>
                    <p class="text-lg font-bold text-white">{{ $maxDaysAdvance }} dias</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-lg border border-zinc-700 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-900/50">
                    <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">Aviso minimo</p>
                    <p class="text-lg font-bold text-white">{{ $minHoursNotice }} horas</p>
                </div>
            </div>
        </div>

        {{-- My Upcoming Reservations --}}
        @if ($myUpcomingReservations->isNotEmpty())
            <div class="mt-6">
                <h3 class="mb-4 text-lg font-semibold text-white">Mis proximas reservas</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($myUpcomingReservations as $reservation)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-700 p-3">
                            <div>
                                <p class="font-semibold text-white">
                                    {{ $reservation->date->format('d/m/Y') }}
                                </p>
                                <p class="text-sm text-zinc-400">
                                    {{ $reservation->time_range }}
                                </p>
                                <p class="mt-1 text-lg font-bold text-green-400">
                                    ${{ number_format($reservation->total_amount, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-900/50 text-amber-200',
                                        'approved' => 'bg-green-900/50 text-green-200',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$reservation->status] ?? '' }}">
                                    {{ $reservation->status_label }}
                                </span>
                                @if (in_array($reservation->status, ['pending', 'approved']))
                                    <button wire:click="openCancelModal({{ $reservation->id }})"
                                        class="rounded p-1 text-red-400 hover:bg-red-900/20">
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
                                @if($selectedDate)
                                    {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                                @endif
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

    {{-- FullCalendar CSS --}}
    <style>
        .fc {
            --fc-border-color: rgb(63 63 70);
            --fc-button-bg-color: #3b82f6;
            --fc-button-border-color: #3b82f6;
            --fc-button-hover-bg-color: #2563eb;
            --fc-button-hover-border-color: #2563eb;
            --fc-button-active-bg-color: #1d4ed8;
            --fc-today-bg-color: rgba(59, 130, 246, 0.15);
            --fc-event-border-color: transparent;
            --fc-page-bg-color: rgb(24 24 27);
            --fc-neutral-bg-color: rgb(39 39 42);
            --fc-list-event-hover-bg-color: rgb(63 63 70);
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
        }
        .fc .fc-col-header-cell-cushion,
        .fc .fc-daygrid-day-number,
        .fc .fc-timegrid-axis-cushion,
        .fc .fc-timegrid-slot-label-cushion,
        .fc .fc-list-day-cushion,
        .fc .fc-list-event-time,
        .fc .fc-list-event-title {
            color: white;
        }
        .fc .fc-scrollgrid,
        .fc .fc-scrollgrid-section > td {
            background-color: rgb(24 24 27);
        }
        .fc .fc-timegrid-slot,
        .fc .fc-daygrid-day {
            background-color: rgb(24 24 27);
        }
        .fc .fc-col-header-cell {
            background-color: rgb(39 39 42);
        }
        .fc .fc-timegrid-axis {
            background-color: rgb(39 39 42);
        }
        .fc .fc-button {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
        }
        .fc .fc-timegrid-slot {
            height: 3rem;
        }
        .fc .fc-timegrid-slot-minor {
            border-top-style: none;
        }
        .fc-event {
            cursor: pointer;
            border-radius: 0.375rem;
            padding: 2px 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .fc .fc-timegrid-col.fc-day-today {
            background-color: rgba(59, 130, 246, 0.05);
        }
        .fc-direction-ltr .fc-timegrid-slot-label-frame {
            text-align: center;
        }
        .fc .fc-scrollgrid {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.5rem;
            }
            .fc .fc-toolbar-title {
                font-size: 1rem;
            }
            .fc .fc-button {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
            }
        }
    </style>

    {{-- FullCalendar JS - loaded directly --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js" 
            onerror="console.error('Failed to load FullCalendar'); this.parentElement.innerHTML += '<div class=\'p-4 bg-red-500 text-white rounded\'>Error: No se pudo cargar FullCalendar. Verifique su conexión a internet.</div>';">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js" 
            onerror="console.warn('Failed to load Spanish locale for FullCalendar');">
    </script>

    <script>
        function calendarApp() {
            return {
                calendar: null,
                events: [],
                isResponsible: false,
                openTime: '09:00',
                closeTime: '23:00',
                maxDaysAdvance: 30,
                loading: true,

                setupEventListeners() {
                    const self = this;

                    // Listen for Livewire refresh-calendar event
                    this.$wire.$on('refresh-calendar', (data) => {
                        console.log('Received refresh-calendar event:', data);
                        const events = data.events || data[0]?.events || [];
                        self.refreshEvents(events);
                    });
                },

                initCalendar() {
                    console.log('Initializing calendar app...');

                    // Load config from window
                    if (window.sumCalendarConfig) {
                        this.events = window.sumCalendarConfig.events || [];
                        this.isResponsible = window.sumCalendarConfig.isResponsible || false;
                        this.openTime = window.sumCalendarConfig.openTime || '09:00';
                        this.closeTime = window.sumCalendarConfig.closeTime || '23:00';
                        this.maxDaysAdvance = window.sumCalendarConfig.maxDaysAdvance || 30;

                        console.log('Calendar config loaded:', {
                            eventsCount: this.events.length,
                            isResponsible: this.isResponsible,
                            openTime: this.openTime,
                            closeTime: this.closeTime,
                            events: this.events
                        });
                    }

                    this.$nextTick(() => {
                        this.loadCalendar();
                    });
                },

                loadCalendar() {
                    console.log('Loading FullCalendar...');

                    // Wait for FullCalendar to be loaded
                    if (typeof FullCalendar === 'undefined') {
                        console.warn('FullCalendar not loaded yet, retrying...');
                        setTimeout(() => this.loadCalendar(), 100);
                        return;
                    }

                    const calendarEl = document.getElementById('fullcalendar');
                    if (!calendarEl) {
                        console.warn('Calendar element not found, retrying...');
                        setTimeout(() => this.loadCalendar(), 100);
                        return;
                    }

                    // If calendar already exists, destroy it first
                    if (this.calendar) {
                        console.log('Destroying existing calendar...');
                        this.calendar.destroy();
                    }

                    console.log('Creating calendar with', this.events.length, 'events');

                    const self = this;
                    const now = new Date();
                    const maxDate = new Date();
                    maxDate.setDate(maxDate.getDate() + this.maxDaysAdvance);

                    // Handle overnight closing times
                    let slotMaxTime = this.closeTime + ':00';
                    const openHour = parseInt(this.openTime.split(':')[0]);
                    const closeHour = parseInt(this.closeTime.split(':')[0]);

                    // If closing time is before opening time, it means it goes to next day
                    if (closeHour < openHour) {
                        // Convert to 24+ hour format (e.g., 02:00 becomes 26:00)
                        const adjustedHour = closeHour + 24;
                        const closeMinute = this.closeTime.split(':')[1];
                        slotMaxTime = adjustedHour + ':' + closeMinute + ':00';
                    }

                    console.log('Slot times:', {
                        slotMinTime: this.openTime + ':00',
                        slotMaxTime: slotMaxTime
                    });

                    try {
                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                            locale: 'es',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'timeGridDay,timeGridWeek,dayGridMonth'
                            },
                            buttonText: {
                                today: 'Hoy',
                                day: 'Dia',
                                week: 'Semana',
                                month: 'Mes'
                            },
                            slotMinTime: this.openTime + ':00',
                            slotMaxTime: slotMaxTime,
                            slotDuration: '01:00:00',
                            slotLabelInterval: '01:00:00',
                            slotLabelFormat: {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            },
                            allDaySlot: false,
                            nowIndicator: true,
                            selectable: this.isResponsible,
                            selectMirror: true,
                            selectOverlap: false,
                            selectAllow: function(selectInfo) {
                                // Don't allow selection in the past
                                if (selectInfo.start < now) return false;
                                // Don't allow selection beyond max days
                                if (selectInfo.start > maxDate) return false;
                                return true;
                            },
                            validRange: {
                                start: new Date(now.getFullYear(), now.getMonth(), now.getDate()),
                                end: maxDate
                            },
                            events: this.events,
                            eventClick: function(info) {
                                const props = info.event.extendedProps;
                                if (props.isOwn) {
                                    self.$wire.call('eventClicked', props.reservationId);
                                }
                            },
                            select: function(info) {
                                if (!self.isResponsible) return;

                                const startDate = info.start.toISOString().split('T')[0];
                                const startTime = info.start.toTimeString().slice(0, 5);
                                const endTime = info.end.toTimeString().slice(0, 5);

                                self.$wire.call('dateSelected', startDate, startTime, endTime);

                                self.calendar.unselect();
                            },
                            dateClick: function(info) {
                                if (!self.isResponsible) return;
                                if (info.date < now) return;
                                if (info.date > maxDate) return;

                                // If in month view, switch to day view
                                if (self.calendar.view.type === 'dayGridMonth') {
                                    self.calendar.changeView('timeGridDay', info.dateStr);
                                }
                            },
                            eventDidMount: function(info) {
                                // Add tooltip
                                const props = info.event.extendedProps;
                                info.el.title = props.isOwn
                                    ? 'Mi reserva - Clic para cancelar'
                                    : 'Reservado: ' + props.unitName;
                            },
                            height: 'auto',
                            expandRows: true,
                            stickyHeaderDates: true,
                            dayMaxEvents: true,
                            windowResize: function(view) {
                                if (window.innerWidth < 768) {
                                    self.calendar.changeView('timeGridDay');
                                }
                            }
                        });

                        this.calendar.render();
                        this.loading = false;
                        console.log('Calendar rendered successfully');
                    } catch (error) {
                        console.error('Error initializing calendar:', error);
                        this.loading = false;
                    }
                },

                refreshEvents(newEventsData) {
                    if (!this.calendar) {
                        console.warn('Calendar not initialized yet');
                        return;
                    }

                    console.log('Refreshing calendar events...', newEventsData);

                    let newEvents = [];

                    // Handle different data formats
                    if (Array.isArray(newEventsData)) {
                        newEvents = newEventsData;
                    } else if (newEventsData?.events) {
                        newEvents = Array.isArray(newEventsData.events) ? newEventsData.events : [];
                    }

                    // Update events array
                    this.events = newEvents;

                    // Remove all existing events
                    this.calendar.getEvents().forEach(event => event.remove());

                    // Add new events
                    if (Array.isArray(newEvents) && newEvents.length > 0) {
                        newEvents.forEach(event => {
                            this.calendar.addEvent(event);
                        });
                    }

                    console.log('Calendar events refreshed:', newEvents.length, 'events');
                }
            }
        }
    </script>
</div>
