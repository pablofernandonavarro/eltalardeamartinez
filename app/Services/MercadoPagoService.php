<?php

namespace App\Services;

use App\Models\SumPayment;
use App\Models\SumReservation;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Payment\PaymentRefundClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Net\MPSearchRequest;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function createPreference(SumReservation $reservation, SumPayment $payment): array
    {
        $reservation->loadMissing(['unit.building', 'user']);

        $unit = $reservation->unit->full_identifier;
        $date = $reservation->date->format('d/m/Y');
        $timeRange = $reservation->time_range;

        $client = new PreferenceClient;

        $isHttps = str_starts_with(config('app.url'), 'https://');

        $data = [
            'items' => [
                [
                    'id' => (string) $reservation->id,
                    'title' => "Reserva SUM - {$unit} - {$date} {$timeRange}",
                    'quantity' => 1,
                    'unit_price' => (float) $reservation->total_amount,
                    'currency_id' => 'ARS',
                ],
            ],
            'payer' => [
                'name' => $reservation->user->name,
                'email' => $reservation->user->email,
            ],
            'back_urls' => [
                'success' => route('resident.sum.payment.result', 'success'),
                'failure' => route('resident.sum.payment.result', 'failure'),
                'pending' => route('resident.sum.payment.result', 'pending'),
            ],
            'external_reference' => "sum_payment_{$payment->id}",
            'notification_url' => url('/api/mp/webhook'),
        ];

        if ($isHttps) {
            $data['auto_return'] = 'approved';
        }

        $preference = $client->create($data);

        return [
            'preference_id' => $preference->id,
            'init_point' => $preference->init_point,
            'sandbox_init_point' => $preference->sandbox_init_point,
        ];
    }

    public function getPayment(string|int $paymentId): object
    {
        $client = new PaymentClient;

        return $client->get((int) $paymentId);
    }

    public function refundPayment(string|int $paymentId): object
    {
        $client = new PaymentRefundClient;

        return $client->refundTotal((int) $paymentId);
    }

    /**
     * Busca en MP el pago aprobado asociado a un SumPayment por external_reference.
     * Retorna el objeto de pago MP si encontró uno aprobado, o null si no hay nada.
     */
    public function findApprovedPayment(int $paymentDbId): ?object
    {
        $client = new PaymentClient;

        $search = $client->search(
            new MPSearchRequest(1, 0, ['external_reference' => "sum_payment_{$paymentDbId}"])
        );

        $results = $search->results ?? [];

        foreach ($results as $mpPayment) {
            if (($mpPayment->status ?? '') === 'approved') {
                return $mpPayment;
            }
        }

        return null;
    }
}
