<?php

namespace App\Http\Controllers;

use App\Enums\SumPaymentStatus;
use App\Enums\SumReservationStatus;
use App\Models\SumPayment;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request, MercadoPagoService $mpService): JsonResponse
    {
        $type = $request->input('type');
        $paymentId = $request->input('data.id');

        if ($type !== 'payment' || ! $paymentId) {
            return response()->json(['ok' => true]);
        }

        try {
            $mpPayment = $mpService->getPayment($paymentId);

            $externalRef = $mpPayment->external_reference ?? null;

            if (! $externalRef || ! str_starts_with($externalRef, 'sum_payment_')) {
                return response()->json(['ok' => true]);
            }

            $paymentDbId = (int) str_replace('sum_payment_', '', $externalRef);
            $payment = SumPayment::with('reservation')->find($paymentDbId);

            if (! $payment || $payment->status === SumPaymentStatus::Paid) {
                return response()->json(['ok' => true]);
            }

            $payment->update([
                'mp_payment_id' => (string) $paymentId,
                'mp_status' => $mpPayment->status,
            ]);

            if ($mpPayment->status === 'approved') {
                $payment->update([
                    'status' => SumPaymentStatus::Paid,
                    'payment_method' => 'online',
                    'transaction_reference' => (string) $paymentId,
                    'paid_at' => now(),
                ]);

                $payment->reservation->update([
                    'status' => SumReservationStatus::Approved,
                    'approved_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('MercadoPago webhook error', [
                'message' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
