<div class="p-4 lg:p-6" x-data="calendarApp()" x-init="initCalendar()">
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
        <div class="mb-4 flex flex-wrap items-center gap-4 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
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
                <span class="h-4 w-4 rounded border-2 border-dashed border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-800"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Disponible</span>
            </div>
        </div>

        {{-- FullCalendar Container --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div id="fullcalendar" class="p-2 sm:p-4" wire:ignore></div>
        </div>

        {{-- Bottom Info Cards --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            --fc-border-color: rgb(228 228 231);
            --fc-button-bg-color: #3b82f6;
            --fc-button-border-color: #3b82f6;
            --fc-button-hover-bg-color: #2563eb;
            --fc-button-hover-border-color: #2563eb;
            --fc-button-active-bg-color: #1d4ed8;
            --fc-today-bg-color: rgba(59, 130, 246, 0.1);
            --fc-event-border-color: transparent;
            --fc-page-bg-color: transparent;
        }
        .dark .fc {
            --fc-border-color: rgb(63 63 70);
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: rgb(39 39 42);
            --fc-list-event-hover-bg-color: rgb(63 63 70);
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
        }
        .dark .fc .fc-toolbar-title,
        .dark .fc .fc-col-header-cell-cushion,
        .dark .fc .fc-daygrid-day-number,
        .dark .fc .fc-timegrid-axis-cushion,
        .dark .fc .fc-timegrid-slot-label-cushion {
            color: white;
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
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js"></script>

    <script>
        function calendarApp() {
            return {
                calendar: null,
                wire: null,
                events: @json($calendarEvents),
                isResponsible: @json($isResponsible),
                openTime: '{{ $openTime }}',
                closeTime: '{{ $closeTime }}',
                maxDaysAdvance: {{ $maxDaysAdvance }},

                initCalendar() {
                    const calendarEl = document.getElementById('fullcalendar');
                    if (!calendarEl) return;

                    // Get $wire reference from Alpine
                    this.wire = this.$wire;
                    const self = this;
                    const now = new Date();
                    const maxDate = new Date();
                    maxDate.setDate(maxDate.getDate() + this.maxDaysAdvance);

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
                        slotMaxTime: this.closeTime + ':00',
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
                                self.wire.eventClicked(props.reservationId);
                            }
                        },
                        select: function(info) {
                            if (!self.isResponsible) return;

                            const startDate = info.start.toISOString().split('T')[0];
                            const startTime = info.start.toTimeString().slice(0, 5);
                            const endTime = info.end.toTimeString().slice(0, 5);

                            self.wire.dateSelected(startDate, startTime, endTime);

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

                    // Listen for refresh event from Livewire
                    Livewire.on('refreshCalendar', (data) => {
                        const eventsJson = data.events || data[0]?.events || '[]';
                        const newEvents = typeof eventsJson === 'string' ? JSON.parse(eventsJson) : eventsJson;
                        this.events = newEvents;

                        // Remove all existing events
                        this.calendar.getEvents().forEach(event => event.remove());

                        // Add new events
                        newEvents.forEach(event => {
                            this.calendar.addEvent(event);
                        });
                    });
                }
            }
        }
    </script>
</div>
