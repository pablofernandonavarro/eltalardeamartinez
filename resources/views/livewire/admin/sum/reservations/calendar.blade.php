<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Calendario de Reservas SUM</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Vista de todas las reservas del Salón de Usos Múltiples</p>
        </div>
        <flux:button href="{{ route('admin.sum.reservations.index') }}" variant="ghost" icon="list-bullet">
            Ver Lista
        </flux:button>
    </div>

    {{-- Leyenda de colores --}}
    <div class="mb-4 flex flex-wrap items-center gap-4 rounded-lg border border-zinc-700 p-3">
        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Leyenda:</span>
        <div class="flex items-center gap-2">
            <span class="h-4 w-4 rounded" style="background-color: #f59e0b;"></span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Pendiente</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-4 w-4 rounded" style="background-color: #10b981;"></span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Aprobada</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-4 w-4 rounded" style="background-color: #3b82f6;"></span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Completada</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-4 w-4 rounded" style="background-color: #ef4444;"></span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Rechazada</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="h-4 w-4 rounded" style="background-color: #71717a;"></span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Cancelada</span>
        </div>
    </div>

    {{-- Calendario --}}
    <div class="rounded-xl border border-zinc-700">
        <div id="fullcalendar" class="min-h-[400px]"></div>
    </div>

    {{-- Modal de Detalles --}}
    <flux:modal name="reservation-details" :open="$showDetailsModal" wire:model="showDetailsModal" class="min-w-[600px]">
        @if($selectedReservation)
            <div>
                <flux:heading size="lg" class="mb-4">Detalles de la Reserva</flux:heading>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:subheading class="mb-1">Usuario</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->user->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $selectedReservation->user->email }}</p>
                        </div>
                        <div>
                            <flux:subheading class="mb-1">Unidad</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->unit->full_identifier }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:subheading class="mb-1">Fecha</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <flux:subheading class="mb-1">Horario</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->time_range }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:subheading class="mb-1">Estado</flux:subheading>
                            <flux:badge :color="$selectedReservation->status_color" size="sm">
                                {{ $selectedReservation->status_label }}
                            </flux:badge>
                        </div>
                        <div>
                            <flux:subheading class="mb-1">Monto Total</flux:subheading>
                            <p class="text-sm font-semibold">${{ number_format($selectedReservation->total_amount, 2) }}</p>
                        </div>
                    </div>

                    <div>
                        <flux:subheading class="mb-1">Duración</flux:subheading>
                        <p class="text-sm">{{ $selectedReservation->total_hours }} horas ({{ number_format($selectedReservation->price_per_hour, 2) }}/hora)</p>
                    </div>

                    @if($selectedReservation->notes)
                        <div>
                            <flux:subheading class="mb-1">Notas del Usuario</flux:subheading>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedReservation->notes }}</p>
                        </div>
                    @endif

                    @if($selectedReservation->admin_notes)
                        <div>
                            <flux:subheading class="mb-1">Notas del Administrador</flux:subheading>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedReservation->admin_notes }}</p>
                        </div>
                    @endif

                    @if($selectedReservation->status === 'approved' && $selectedReservation->approvedBy)
                        <div>
                            <flux:subheading class="mb-1">Aprobada por</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->approvedBy->name }} - {{ $selectedReservation->approved_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif

                    @if($selectedReservation->status === 'rejected')
                        <div>
                            <flux:subheading class="mb-1">Rechazada por</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->rejectedBy->name }} - {{ $selectedReservation->rejected_at->format('d/m/Y H:i') }}</p>
                            @if($selectedReservation->rejection_reason)
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">Motivo: {{ $selectedReservation->rejection_reason }}</p>
                            @endif
                        </div>
                    @endif

                    @if($selectedReservation->status === 'cancelled')
                        <div>
                            <flux:subheading class="mb-1">Cancelada por</flux:subheading>
                            <p class="text-sm">{{ $selectedReservation->cancelledBy->name }} - {{ $selectedReservation->cancelled_at->format('d/m/Y H:i') }}</p>
                            @if($selectedReservation->cancellation_reason)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Motivo: {{ $selectedReservation->cancellation_reason }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <flux:button wire:click="closeDetailsModal" variant="ghost">
                        Cerrar
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- FullCalendar CSS --}}
    <style>
        #fullcalendar {
            width: 100%;
        }
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
        .fc .fc-header-toolbar {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            margin-bottom: 1rem;
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
            text-transform: capitalize;
        }
        .fc .fc-button:not(:disabled) {
            cursor: pointer;
        }
        .fc .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
        .fc .fc-toolbar {
            padding: 1rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }
        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.75rem;
            }
            .fc .fc-toolbar-chunk {
                width: 100%;
                justify-content: center;
            }
            .fc .fc-toolbar-title {
                font-size: 1rem;
                text-align: center;
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
        function initSumCalendar() {
            // Wait for FullCalendar to be loaded
            if (typeof FullCalendar === 'undefined') {
                console.log('Waiting for FullCalendar to load...');
                setTimeout(initSumCalendar, 100);
                return;
            }

            const calendarEl = document.getElementById('fullcalendar');
            if (!calendarEl) {
                console.warn('Calendar element not found');
                setTimeout(initSumCalendar, 100);
                return;
            }

            // Check if calendar is already initialized
            if (calendarEl.classList.contains('fc')) {
                console.log('Calendar already initialized');
                return;
            }

            // Get times from Livewire component
            const openTime = '{{ $openTime }}';
            const closeTime = '{{ $closeTime }}';

            // Handle overnight closing times
            let slotMaxTime = closeTime + ':00';
            const openHour = parseInt(openTime.split(':')[0]);
            const closeHour = parseInt(closeTime.split(':')[0]);

            // If closing time is before opening time, it means it goes to next day
            if (closeHour < openHour) {
                // Convert to 24+ hour format (e.g., 02:00 becomes 26:00)
                const adjustedHour = closeHour + 24;
                const closeMinute = closeTime.split(':')[1];
                slotMaxTime = adjustedHour + ':' + closeMinute + ':00';
            }

            console.log('Slot times:', {
                slotMinTime: openTime + ':00',
                slotMaxTime: slotMaxTime,
                openTime: openTime,
                closeTime: closeTime
            });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                views: {
                    dayGridMonth: { buttonText: 'Mes' },
                    timeGridWeek: { buttonText: 'Semana' },
                    timeGridDay: { buttonText: 'Día' }
                },
                slotMinTime: openTime + ':00',
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
                selectable: false,
                events: function(info, successCallback, failureCallback) {
                    fetch(`/api/sum/reservations/events?start=${info.startStr}&end=${info.endStr}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Events loaded:', data);
                        successCallback(Array.isArray(data) ? data : []);
                    })
                    .catch(error => {
                        console.error('Error loading events:', error);
                        failureCallback(error);
                    });
                },
                eventClick: function(info) {
                    const reservationId = info.event.extendedProps.reservation_id;
                    if (reservationId) {
                        @this.call('viewReservation', reservationId);
                    }
                },
                eventDidMount: function(info) {
                    // Add tooltip
                    const props = info.event.extendedProps;
                    info.el.title = `${props.user_name} - ${props.unit}\n${props.status_label} - ${props.time_range}`;
                },
                height: 'auto',
                expandRows: true,
                stickyHeaderDates: true,
                dayMaxEvents: true,
                windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('timeGridDay');
                    }
                }
            });

            calendar.render();
            console.log('Calendar rendered successfully');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initSumCalendar);

        // Initialize on Livewire navigation
        document.addEventListener('livewire:navigated', initSumCalendar);
    </script>
</div>
