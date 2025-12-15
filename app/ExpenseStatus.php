<?php

namespace App;

enum ExpenseStatus: string
{
    case Pendiente = 'pendiente';
    case Parcial = 'parcial';
    case Pagada = 'pagada';
    case Vencida = 'vencida';

    /**
     * Get the display label for the expense status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Parcial => 'Parcial',
            self::Pagada => 'Pagada',
            self::Vencida => 'Vencida',
        };
    }

    /**
     * Get the color class for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'yellow',
            self::Parcial => 'blue',
            self::Pagada => 'green',
            self::Vencida => 'red',
        };
    }

    /**
     * Check if the expense is fully paid.
     */
    public function isPaid(): bool
    {
        return $this === self::Pagada;
    }

    /**
     * Check if the expense has any payment.
     */
    public function hasPayment(): bool
    {
        return in_array($this, [self::Parcial, self::Pagada]);
    }
}
