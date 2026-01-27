<?php

namespace App\Livewire\Admin\Sum\Reservations;

use App\Models\SumReservation;
use Livewire\Component;

class Calendar extends Component
{
    public bool $showDetailsModal = false;

    public ?int $selectedReservationId = null;

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
        ])->layout('components.layouts.app', ['title' => 'Calendario de Reservas SUM']);
    }
}
