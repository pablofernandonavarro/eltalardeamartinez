<?php

namespace App\Livewire\Resident\Sum;

use App\Models\SumReservation;
use App\Models\SumSetting;
use Carbon\Carbon;
use Livewire\Component;

class Reservations extends Component
{
    public ?int $unitId = null;

    public bool $isResponsible = false;

    // Selected date/time from calendar
    public ?string $selectedDate = null;

    public ?string $selectedStartTime = null;

    public ?string $selectedEndTime = null;

    // Reservation modal
    public bool $showCreateModal = false;

    public string $startTime = '';

    public string $endTime = '';

    public string $notes = '';

    // Cancel modal
    public bool $showCancelModal = false;

    public ?int $cancelReservationId = null;

    public string $cancellationReason = '';

    // Settings
    public int $pricePerHour;

    public string $openTime;

    public string $closeTime;

    public int $maxDaysAdvance;

    public int $minHoursNotice;

    public bool $requiresApproval;

    public function mount(): void
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->get();

        if ($unitUsers->isNotEmpty()) {
            $this->unitId = $unitUsers->first()->unit_id;
            $this->checkResponsible();
        }

        // Load settings
        $this->pricePerHour = SumSetting::get('price_per_hour', 500);
        $this->openTime = SumSetting::get('open_time', '09:00');
        $this->closeTime = SumSetting::get('close_time', '23:00');
        $this->maxDaysAdvance = SumSetting::get('max_days_advance', 30);
        $this->minHoursNotice = SumSetting::get('min_hours_notice', 24);
        $this->requiresApproval = SumSetting::get('requires_approval', false);
    }

    public function updatedUnitId(): void
    {
        $this->checkResponsible();
        $this->dispatchCalendarRefresh();
    }

    protected function dispatchCalendarRefresh(): void
    {
        $this->dispatch('refresh-calendar', events: $this->getCalendarEventsProperty());
    }

    protected function checkResponsible(): void
    {
        if (! $this->unitId) {
            $this->isResponsible = false;

            return;
        }

        $user = auth()->user();
        $this->isResponsible = $user->currentUnitUsers()
            ->where('unit_id', $this->unitId)
            ->where('is_responsible', true)
            ->exists();
    }

    public function dateSelected(string $date, ?string $startTime = null, ?string $endTime = null): void
    {
        $this->selectedDate = $date;
        $this->selectedStartTime = $startTime;
        $this->selectedEndTime = $endTime;

        if ($this->isResponsible && $startTime) {
            $this->openCreateModalWithTime($date, $startTime, $endTime);
        }
    }

    public function openCreateModalWithTime(string $date, string $startTime, ?string $endTime = null): void
    {
        if (! $this->isResponsible) {
            return;
        }

        $this->selectedDate = $date;
        $this->startTime = $startTime;
        $this->endTime = $endTime ?? '';
        $this->notes = '';
        $this->showCreateModal = true;
    }

    public function eventClicked(int $reservationId): void
    {
        $reservation = SumReservation::find($reservationId);
        if ($reservation && $reservation->user_id === auth()->id()) {
            $this->openCancelModal($reservationId);
        }
    }

    public function openCreateModal(): void
    {
        if (! $this->isResponsible || ! $this->selectedDate) {
            return;
        }

        $this->startTime = $this->selectedStartTime ?? $this->openTime;
        $this->endTime = $this->selectedEndTime ?? '';
        $this->notes = '';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createReservation(): void
    {
        if (! $this->isResponsible) {
            session()->flash('error', 'Solo el responsable de pago puede realizar reservas.');

            return;
        }

        $this->validate([
            'selectedDate' => 'required|date|after_or_equal:today',
            'startTime' => 'required',
            'endTime' => 'required|after:startTime',
            'notes' => 'nullable|string|max:500',
        ], [
            'selectedDate.required' => 'Debe seleccionar una fecha.',
            'selectedDate.after_or_equal' => 'No puede reservar fechas pasadas.',
            'startTime.required' => 'Debe seleccionar hora de inicio.',
            'endTime.required' => 'Debe seleccionar hora de fin.',
            'endTime.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        // Validate time range
        if ($this->startTime < $this->openTime || $this->endTime > $this->closeTime) {
            $this->addError('startTime', "El horario permitido es de {$this->openTime} a {$this->closeTime}.");

            return;
        }

        // Validate max days advance
        $selectedDate = Carbon::parse($this->selectedDate);
        $maxDate = now()->addDays($this->maxDaysAdvance);
        if ($selectedDate->gt($maxDate)) {
            $this->addError('selectedDate', "Solo puede reservar con {$this->maxDaysAdvance} dias de anticipacion.");

            return;
        }

        // Validate min hours notice
        $reservationDateTime = Carbon::parse($this->selectedDate.' '.$this->startTime);
        $minDateTime = now()->addHours($this->minHoursNotice);
        if ($reservationDateTime->lt($minDateTime)) {
            $this->addError('startTime', "Debe reservar con al menos {$this->minHoursNotice} horas de anticipacion.");

            return;
        }

        // Check for overlap
        if (SumReservation::hasOverlap($this->selectedDate, $this->startTime, $this->endTime)) {
            $this->addError('startTime', 'Ya existe una reserva en ese horario.');

            return;
        }

        // Calculate hours and amount
        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);
        $totalHours = $end->diffInMinutes($start) / 60;
        $totalAmount = $totalHours * $this->pricePerHour;

        $status = $this->requiresApproval ? 'pending' : 'approved';

        SumReservation::create([
            'unit_id' => $this->unitId,
            'user_id' => auth()->id(),
            'date' => $this->selectedDate,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'total_hours' => $totalHours,
            'price_per_hour' => $this->pricePerHour,
            'total_amount' => $totalAmount,
            'status' => $status,
            'notes' => $this->notes,
            'approved_at' => $this->requiresApproval ? null : now(),
            'approved_by' => $this->requiresApproval ? null : auth()->id(),
        ]);

        $this->closeCreateModal();
        $this->dispatchCalendarRefresh();

        $message = $this->requiresApproval
            ? 'Reserva creada. Pendiente de aprobacion.'
            : 'Reserva confirmada exitosamente.';
        session()->flash('message', $message);
    }

    public function openCancelModal(int $reservationId): void
    {
        $this->cancelReservationId = $reservationId;
        $this->cancellationReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelReservationId = null;
        $this->cancellationReason = '';
    }

    public function cancelReservation(): void
    {
        if (! $this->cancelReservationId) {
            return;
        }

        $reservation = SumReservation::query()
            ->where('id', $this->cancelReservationId)
            ->where('user_id', auth()->id())
            ->active()
            ->firstOrFail();

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $this->cancellationReason,
        ]);

        $this->closeCancelModal();
        $this->dispatchCalendarRefresh();
        session()->flash('message', 'Reserva cancelada exitosamente.');
    }

    public function getCalendarEventsProperty(): array
    {
        $events = [];

        // Get all active reservations for the next 60 days
        $reservations = SumReservation::query()
            ->with(['unit.building', 'user'])
            ->active()
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->where('date', '<=', now()->addDays(60)->toDateString())
            ->get();

        foreach ($reservations as $reservation) {
            $isOwn = $reservation->user_id === auth()->id();
            $startTime = is_string($reservation->start_time)
                ? $reservation->start_time
                : $reservation->start_time->format('H:i:s');
            $endTime = is_string($reservation->end_time)
                ? $reservation->end_time
                : $reservation->end_time->format('H:i:s');

            $events[] = [
                'id' => $reservation->id,
                'title' => $isOwn
                    ? 'Mi reserva'
                    : ($reservation->unit->building->name ?? 'UF').' - '.$reservation->unit->number,
                'start' => $reservation->date->format('Y-m-d').'T'.$startTime,
                'end' => $reservation->date->format('Y-m-d').'T'.$endTime,
                'backgroundColor' => $isOwn ? '#3b82f6' : '#f59e0b',
                'borderColor' => $isOwn ? '#2563eb' : '#d97706',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'reservationId' => $reservation->id,
                    'isOwn' => $isOwn,
                    'status' => $reservation->status,
                    'unitName' => ($reservation->unit->building->name ?? '').' - '.$reservation->unit->number,
                ],
            ];
        }

        return $events;
    }

    public function getMyUpcomingReservationsProperty()
    {
        if (! $this->unitId) {
            return collect();
        }

        return SumReservation::query()
            ->with(['unit.building'])
            ->where('user_id', auth()->id())
            ->where('unit_id', $this->unitId)
            ->active()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    public function getAvailableTimeSlotsProperty(): array
    {
        $slots = [];
        $start = Carbon::parse($this->openTime);
        $end = Carbon::parse($this->closeTime);

        // Si el horario de cierre es menor que el de apertura, significa que cruza medianoche
        if ($end->lt($start)) {
            $end->addDay();
        }

        while ($start->lt($end)) {
            $slots[] = $start->format('H:i');
            $start->addHour();
        }

        // Agregar el horario de cierre como última opción
        $slots[] = $this->closeTime;

        return $slots;
    }

    public function getCalculatedAmountProperty(): float
    {
        if (! $this->startTime || ! $this->endTime || $this->startTime >= $this->endTime) {
            return 0;
        }

        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);
        $hours = $end->diffInMinutes($start) / 60;

        return $hours * $this->pricePerHour;
    }

    public function getCalculatedHoursProperty(): float
    {
        if (! $this->startTime || ! $this->endTime || $this->startTime >= $this->endTime) {
            return 0;
        }

        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);

        return $end->diffInMinutes($start) / 60;
    }

    public function render()
    {
        $user = auth()->user();
        $unitUsers = $user->currentUnitUsers()->with(['unit.building'])->get();

        return view('livewire.resident.sum.reservations', [
            'unitUsers' => $unitUsers,
            'calendarEvents' => $this->getCalendarEventsProperty(),
            'myUpcomingReservations' => $this->myUpcomingReservations,
            'availableTimeSlots' => $this->availableTimeSlots,
            'calculatedAmount' => $this->calculatedAmount,
            'calculatedHours' => $this->calculatedHours,
        ])->layout('components.layouts.resident', ['title' => 'Reservar SUM']);
    }
}
