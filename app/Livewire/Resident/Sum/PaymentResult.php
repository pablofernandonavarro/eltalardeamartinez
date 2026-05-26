<?php

namespace App\Livewire\Resident\Sum;

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

        // Fallback: find latest pending/approved reservation for this user
        if (! $this->reservation) {
            $this->reservation = SumReservation::with('unit.building')
                ->where('user_id', auth()->id())
                ->whereIn('status', ['pending', 'approved'])
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
