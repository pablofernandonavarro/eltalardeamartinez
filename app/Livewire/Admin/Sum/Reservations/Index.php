<?php

namespace App\Livewire\Admin\Sum\Reservations;

use App\Enums\SumPaymentStatus;
use App\Enums\SumReservationStatus;
use App\Models\SumPayment;
use App\Models\SumReservation;
use App\Services\MercadoPagoService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filters
    public string $status = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $search = '';

    // Modal states
    public bool $showDetailsModal = false;

    public ?int $selectedReservationId = null;

    public string $rejectionReason = '';

    public string $paymentMethod = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['status', 'dateFrom', 'dateTo', 'search']);
        $this->resetPage();
    }

    public function viewDetails(int $reservationId): void
    {
        $this->selectedReservationId = $reservationId;
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedReservationId = null;
        $this->rejectionReason = '';
        $this->paymentMethod = '';
    }

    public function registerPayment(): void
    {
        $this->validate([
            'paymentMethod' => 'required|in:cash,transfer,card',
        ], [
            'paymentMethod.required' => 'Seleccioná un método de pago.',
        ]);

        $reservation = SumReservation::with('payment')->find($this->selectedReservationId);

        if (! $reservation) {
            return;
        }

        $payment = $reservation->payment;

        if (! $payment) {
            $payment = SumPayment::create([
                'reservation_id' => $reservation->id,
                'amount'         => $reservation->total_amount,
                'status'         => SumPaymentStatus::Pending,
            ]);
        }

        if ($payment->status !== SumPaymentStatus::Pending) {
            session()->flash('error', 'Este pago ya fue procesado.');
            return;
        }

        $payment->markAsPaid($this->paymentMethod);

        session()->flash('message', 'Pago registrado exitosamente.');
        $this->closeDetailsModal();
    }

    public function syncReservationPayment(int $reservationId): void
    {
        $reservation = SumReservation::with('payment')->find($reservationId);
        $payment = $reservation?->payment;

        if (! $payment || ! $payment->mp_preference_id) {
            session()->flash('error', 'Esta reserva no tiene un pago MP asociado para sincronizar.');

            return;
        }

        try {
            $mpPayment = app(MercadoPagoService::class)->findApprovedPayment($payment->id);

            if (! $mpPayment) {
                session()->flash('error', 'No se encontró un pago aprobado en Mercado Pago para esta reserva.');

                return;
            }

            $payment->update([
                'status'                => SumPaymentStatus::Paid,
                'mp_payment_id'         => (string) $mpPayment->id,
                'mp_status'             => $mpPayment->status,
                'payment_method'        => 'online',
                'transaction_reference' => (string) $mpPayment->id,
                'paid_at'               => now(),
            ]);

            $reservation->update([
                'status'      => SumReservationStatus::Approved,
                'approved_at' => now(),
            ]);

            session()->flash('message', 'Pago sincronizado y reserva aprobada. ID MP: ' . $mpPayment->id);
            $this->closeDetailsModal();
        } catch (\Throwable $e) {
            \Log::error('Error al sincronizar pago MP desde reservas', [
                'reservation_id' => $reservationId,
                'error'          => $e->getMessage(),
            ]);
            session()->flash('error', 'Error al consultar Mercado Pago: ' . $e->getMessage());
        }
    }

    public function rejectReservation(): void
    {
        if (! $this->selectedReservationId) {
            return;
        }

        $this->validate([
            'rejectionReason' => 'required|string|max:500',
        ], [
            'rejectionReason.required' => 'Debe indicar el motivo del rechazo.',
        ]);

        $reservation = SumReservation::with('payment')->find($this->selectedReservationId);

        if (! $reservation || $reservation->status !== SumReservationStatus::Pending) {
            session()->flash('error', 'La reserva no puede ser rechazada.');
            $this->closeDetailsModal();

            return;
        }

        $payment = $reservation->payment;
        $refunded = false;

        if ($payment && $payment->status === SumPaymentStatus::Paid && $payment->mp_payment_id) {
            try {
                app(MercadoPagoService::class)->refundPayment($payment->mp_payment_id);
                $payment->update(['status' => SumPaymentStatus::Refunded]);
                $refunded = true;
            } catch (\Throwable $e) {
                \Log::error('Error al reembolsar pago MP', [
                    'payment_id' => $payment->id,
                    'mp_payment_id' => $payment->mp_payment_id,
                    'error' => $e->getMessage(),
                ]);
                session()->flash('error', 'No se pudo procesar el reembolso en Mercado Pago. Verificá manualmente el pago #' . $payment->mp_payment_id . '.');
                $this->closeDetailsModal();

                return;
            }
        }

        $reservation->update([
            'status'           => SumReservationStatus::Rejected,
            'rejected_by'      => auth()->id(),
            'rejected_at'      => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $msg = $refunded
            ? 'Reserva rechazada y pago reembolsado exitosamente.'
            : 'Reserva rechazada exitosamente.';

        session()->flash('message', $msg);
        $this->closeDetailsModal();
    }

    public function getReservationsProperty()
    {
        $query = SumReservation::query()
            ->with(['unit.building', 'user', 'payment']);

        // Apply filters
        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->dateFrom) {
            $query->where('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('date', '<=', $this->dateTo);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                })
                    ->orWhereHas('unit', function ($q) {
                        $q->where('number', 'like', "%{$this->search}%");
                    });
            });
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15);
    }

    public function getSelectedReservationProperty()
    {
        if (! $this->selectedReservationId) {
            return null;
        }

        return SumReservation::with(['unit.building', 'user', 'approvedBy', 'rejectedBy', 'cancelledBy', 'payment'])
            ->find($this->selectedReservationId);
    }

    public function render()
    {
        return view('livewire.admin.sum.reservations.index', [
            'reservations' => $this->reservations,
            'selectedReservation' => $this->selectedReservation,
        ])->layout('components.layouts.app', ['title' => 'Reservas del SUM']);
    }
}
