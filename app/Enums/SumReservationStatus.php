<?php

namespace App\Enums;

enum SumReservationStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pendiente',
            self::Approved  => 'Aprobada',
            self::Rejected  => 'Rechazada',
            self::Cancelled => 'Cancelada',
            self::Completed => 'Completada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'amber',
            self::Approved  => 'green',
            self::Rejected  => 'red',
            self::Cancelled => 'zinc',
            self::Completed => 'blue',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Approved]);
    }
}
