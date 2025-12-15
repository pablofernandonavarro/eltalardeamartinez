<?php

namespace App;

enum PoolStatus: string
{
    case Habilitada = 'habilitada';
    case Inhabilitada = 'inhabilitada';

    /**
     * Get the display label for the pool status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Habilitada => 'Habilitada',
            self::Inhabilitada => 'Inhabilitada',
        };
    }

    /**
     * Get the color class for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Habilitada => 'green',
            self::Inhabilitada => 'red',
        };
    }

    /**
     * Check if the pool is enabled.
     */
    public function isEnabled(): bool
    {
        return $this === self::Habilitada;
    }

    /**
     * Check if the pool is disabled.
     */
    public function isDisabled(): bool
    {
        return $this === self::Inhabilitada;
    }
}
