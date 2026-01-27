<?php

namespace App\Livewire\Admin\Sum\Reservations;

use App\Models\SumReservation;
use App\Models\SumSetting;
use Livewire\Component;

class Calendar extends Component
{
    public bool $showDetailsModal = false;

    public ?int $selectedReservationId = null;

    public string $openTime;

    public string $closeTime;

    public function mount(): void
    {
        // Load settings from database
        $this->openTime = SumSetting::get('open_time', '09:00');
        $this->closeTime = SumSetting::get('close_time', '23:00');
    }

    public function viewReservation(int $reservationId): void
    {
        $this->selectedReservationId = $reservationId;
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedReservationId = null;
    }

    public function getSelectedReservationProperty()
    {
        if (! $this->selectedReservationId) {
            return null;
        }

        return SumReservation::with(['unit.building', 'user', 'approvedBy', 'rejectedBy', 'cancelledBy'])
            ->find($this->selectedReservationId);
    }

    public function render()
    {
        return view('livewire.admin.sum.reservations.calendar', [
            'selectedReservation' => $this->selectedReservation,
            'openTime' => $this->openTime,
            'closeTime' => $this->closeTime,
        ])->layout('components.layouts.app', ['title' => 'Calendario de Reservas SUM']);
    }
}
