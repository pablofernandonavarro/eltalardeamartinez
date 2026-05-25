<?php

namespace App\Enums;

enum SumPaymentStatus: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pendiente',
            self::Paid      => 'Pagado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'amber',
            self::Paid      => 'green',
            self::Cancelled => 'zinc',
        };
    }
}
