<div>
    <div class="mb-6 flex justify-between items-center">
        <flux:heading size="xl">Calendario de Reservas SUM</flux:heading>
        <flux:button href="{{ route('admin.sum.reservations.index') }}" variant="ghost" icon="list-bullet">
            Ver Lista
        </flux:button>
    </div>

    {{-- Leyenda de colores --}}
    <div class="mb-6 p-4 bg-white dark:bg-zinc-800 rounded-lg shadow">
        <div class="flex flex-wrap gap-4 items-center">
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Estados:</span>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded" style="background-color: #f59e0b;"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Pendiente</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded" style="background-color: #10b981;"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Aprobada</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded" style="background-color: #3b82f6;"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Completada</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded" style="background-color: #ef4444;"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Rechazada</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded" style="background-color: #71717a;"></span>
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Cancelada</span>
            </div>
        </div>
    </div>

    {{-- Calendario --}}
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
        <div id="calendar"></div>
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

    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
    @endpush

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js'></script>
    <script>
        document.addEventListener('livewire:navigated', function () {
            const calendarEl = document.getElementById('calendar');

            if (!calendarEl) return;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día',
                    list: 'Lista'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch(`/api/sum/reservations/events?start=${info.startStr}&end=${info.endStr}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => successCallback(data))
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
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '23:00:00',
                allDaySlot: false,
                height: 'auto',
                eventDisplay: 'block',
                displayEventTime: true,
                displayEventEnd: true
            });

            calendar.render();

            // Re-render calendar when modal closes
            window.addEventListener('closeDetailsModal', () => {
                calendar.refetchEvents();
            });
        });
    </script>
    @endpush
</div>
