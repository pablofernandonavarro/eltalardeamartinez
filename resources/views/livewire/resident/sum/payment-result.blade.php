<div class="max-w-xl mx-auto py-10 px-4">

    @if ($status === 'success')
        <div class="flex flex-col items-center gap-6 text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                <flux:icon.check-circle class="w-10 h-10 text-green-600" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">¡Reserva confirmada!</h1>
                <p class="mt-1 text-zinc-500">Tu pago fue procesado y la reserva quedó aprobada.</p>
            </div>

            @if ($reservation)
                <flux:card class="w-full text-left">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Unidad</span>
                            <span class="font-medium">{{ $reservation->unit->full_identifier }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Fecha</span>
                            <span class="font-medium">{{ $reservation->date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Horario</span>
                            <span class="font-medium">{{ $reservation->time_range }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Duración</span>
                            <span class="font-medium">{{ number_format($reservation->total_hours, 1) }} hs</span>
                        </div>
                        <flux:separator />
                        <div class="flex justify-between text-base font-semibold">
                            <span>Total pagado</span>
                            <span class="text-green-600">${{ number_format($reservation->total_amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </flux:card>
            @endif

            <flux:button :href="route('resident.sum.reservations')" wire:navigate variant="primary" icon="calendar">
                Ver mis reservas
            </flux:button>
        </div>

    @elseif ($status === 'pending')
        <div class="flex flex-col items-center gap-6 text-center">
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center">
                <flux:icon.clock class="w-10 h-10 text-amber-600" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Pago en proceso</h1>
                <p class="mt-1 text-zinc-500">Tu pago está siendo procesado. La reserva se confirmará automáticamente cuando acreditemos el pago.</p>
            </div>

            @if ($reservation)
                <flux:callout color="amber" icon="information-circle" class="w-full text-left">
                    Reserva del {{ $reservation->date->format('d/m/Y') }} de {{ $reservation->time_range }} — pendiente de confirmación.
                </flux:callout>
            @endif

            <flux:button :href="route('resident.sum.reservations')" wire:navigate variant="ghost" icon="arrow-left">
                Volver al calendario
            </flux:button>
        </div>

    @else
        <div class="flex flex-col items-center gap-6 text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                <flux:icon.x-circle class="w-10 h-10 text-red-600" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">El pago no se completó</h1>
                <p class="mt-1 text-zinc-500">No se pudo procesar el pago. No se realizó ningún cargo. Podés intentarlo nuevamente.</p>
            </div>

            <flux:button :href="route('resident.sum.reservations')" wire:navigate variant="primary" icon="arrow-path">
                Intentar de nuevo
            </flux:button>
        </div>
    @endif

</div>
