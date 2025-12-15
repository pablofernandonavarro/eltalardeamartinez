<?php

namespace App;

enum PaymentStatus: string
{
    case Pendiente = 'pendiente';
    case Procesado = 'procesado';
    case Anulado = 'anulado';

    /**
     * Get the display label for the payment status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Procesado => 'Procesado',
            self::Anulado => 'Anulado',
        };
    }

    /**
     * Get the color class for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'yellow',
            self::Procesado => 'green',
            self::Anulado => 'red',
        };
    }

    /**
     * Check if the payment is processed.
     */
    public function isProcessed(): bool
    {
        return $this === self::Procesado;
    }

    /**
     * Check if the payment is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this === self::Anulado;
    }
}
