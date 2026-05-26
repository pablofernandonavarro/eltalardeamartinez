<?php

namespace App\Livewire\Resident\Sum;

use App\Enums\SumPaymentStatus;
use App\Enums\SumReservationStatus;
use App\Models\SumPayment;
use App\Models\SumReservation;
use Illuminate\Http\Request;
use Livewire\Component;

class PaymentResult extends Component
{
    public string $status = '';

    public ?SumReservation $reservation = null;

    public ?SumPayment $payment = null;

    public function mount(Request $request, string $status = ''): void
    {
        $this->status = $status;
        $externalRef = $request->query('external_reference');

        if ($externalRef && str_starts_with($externalRef, 'sum_payment_')) {
            $paymentDbId = (int) str_replace('sum_payment_', '', $externalRef);
            $payment = SumPayment::with('reservation.unit.building')->find($paymentDbId);

            if ($payment && $payment->reservation?->user_id === auth()->id()) {
                $this->payment = $payment;
                $this->reservation = $payment->reservation;
            }
        }

        // En caso de fallo, limpiar la reserva y el pago pendientes
        if ($status === 'failure' && $this->reservation) {
            $this->reservation->update([
                'status' => SumReservationStatus::Cancelled,
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => 'Pago rechazado por Mercado Pago',
            ]);
            if ($this->payment) {
                $this->payment->update(['status' => SumPaymentStatus::Cancelled]);
            }
        }

        // Fallback: buscar la última reserva activa del usuario si no se encontró por external_reference
        if (! $this->reservation && $status !== 'failure') {
            $this->reservation = SumReservation::with('unit.building')
                ->where('user_id', auth()->id())
                ->whereIn('status', [SumReservationStatus::Pending, SumReservationStatus::Approved])
                ->latest()
                ->first();
        }
    }

    public function render()
    {
        return view('livewire.resident.sum.payment-result')
            ->layout('components.layouts.resident', ['title' => 'Resultado del Pago']);
    }
}
