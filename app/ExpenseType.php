<?php

namespace App;

enum ExpenseType: string
{
    case Ordinaria = 'ordinaria';
    case Extraordinaria = 'extraordinaria';

    /**
     * Get the display label for the expense type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Ordinaria => 'Ordinaria',
            self::Extraordinaria => 'Extraordinaria',
        };
    }

    /**
     * Check if the expense is ordinaria.
     */
    public function isOrdinaria(): bool
    {
        return $this === self::Ordinaria;
    }

    /**
     * Check if the expense is extraordinaria.
     */
    public function isExtraordinaria(): bool
    {
        return $this === self::Extraordinaria;
    }
}
