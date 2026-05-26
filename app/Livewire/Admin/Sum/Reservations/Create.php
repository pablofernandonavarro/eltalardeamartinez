<?php

namespace App\Livewire\Admin\Sum\Reservations;

use App\Enums\SumPaymentStatus;
use App\Enums\SumReservationStatus;
use App\Models\SumPayment;
use App\Models\SumReservation;
use App\Models\SumSetting;
use App\Models\Unit;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    public ?int $unitId = null;

    public ?int $userId = null;

    public string $date = '';

    public string $startTime = '';

    public string $endTime = '';

    public string $notes = '';

    public bool $markAsPaid = true;

    public int $pricePerHour;

    public string $openTime;

    public string $closeTime;

    public function mount(): void
    {
        $this->pricePerHour = SumSetting::get('price_per_hour', 500);
        $this->openTime = SumSetting::get('open_time', '09:00');
        $this->closeTime = SumSetting::get('close_time', '23:00');
        $this->date = now()->addDay()->toDateString();
        $this->startTime = $this->openTime;
    }

    public function updatedUnitId(): void
    {
        $this->userId = null;
    }

    public function getUsersForUnitProperty()
    {
        if (! $this->unitId) {
            return collect();
        }

        return \App\Models\User::whereHas('currentUnitUsers', fn ($q) => $q->where('unit_id', $this->unitId))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableEndTimeSlotsProperty(): array
    {
        if (! $this->startTime) {
            return [];
        }

        $slots = [];
        $start = Carbon::parse($this->startTime)->addHour();
        $end = Carbon::parse($this->closeTime);

        if ($end->lte(Carbon::parse($this->startTime))) {
            $end->addDay();
        }

        while ($start->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addHour();
        }

        return $slots;
    }

    public function getCalculatedHoursProperty(): float
    {
        if (! $this->startTime || ! $this->endTime) {
            return 0;
        }

        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);

        if ($end->lte($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end) / 60;
    }

    public function getCalculatedAmountProperty(): float
    {
        return $this->calculatedHours * $this->pricePerHour;
    }

    public function save(): void
    {
        $this->validate([
            'unitId' => 'required|exists:units,id',
            'userId' => 'required|exists:users,id',
            'date' => 'required|date',
            'startTime' => 'required',
            'endTime' => 'required',
            'notes' => 'nullable|string|max:500',
        ], [
            'unitId.required' => 'Seleccioná una unidad funcional.',
            'userId.required' => 'Seleccioná un usuario.',
            'date.required' => 'Ingresá la fecha.',
            'startTime.required' => 'Ingresá la hora de inicio.',
            'endTime.required' => 'Ingresá la hora de fin.',
        ]);

        if (SumReservation::hasOverlap($this->date, $this->startTime, $this->endTime)) {
            $this->addError('startTime', 'Ya existe una reserva en ese horario.');

            return;
        }

        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);

        if ($end->lte($start)) {
            $end->addDay();
        }

        $totalHours = $start->diffInMinutes($end) / 60;
        $totalAmount = $totalHours * $this->pricePerHour;

        $reservation = SumReservation::create([
            'unit_id' => $this->unitId,
            'user_id' => $this->userId,
            'date' => $this->date,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'total_hours' => $totalHours,
            'price_per_hour' => $this->pricePerHour,
            'total_amount' => $totalAmount,
            'status' => SumReservationStatus::Approved,
            'notes' => $this->notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        SumPayment::create([
            'reservation_id' => $reservation->id,
            'amount' => $totalAmount,
            'status' => $this->markAsPaid ? SumPaymentStatus::Paid : SumPaymentStatus::Pending,
            'payment_method' => $this->markAsPaid ? 'cash' : null,
            'paid_at' => $this->markAsPaid ? now() : null,
            'paid_by' => $this->markAsPaid ? auth()->id() : null,
        ]);

        session()->flash('message', 'Reserva creada exitosamente.');

        $this->redirect(route('admin.sum.reservations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.sum.reservations.create', [
            'units' => Unit::with('building')->orderBy('number')->get(),
            'usersForUnit' => $this->usersForUnit,
            'availableEndTimeSlots' => $this->availableEndTimeSlots,
            'calculatedHours' => $this->calculatedHours,
            'calculatedAmount' => $this->calculatedAmount,
        ])->layout('components.layouts.app', ['title' => 'Nueva Reserva SUM']);
    }
}
