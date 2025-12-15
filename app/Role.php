<?php

namespace App;

enum Role: string
{
    case Admin = 'admin';
    case Propietario = 'propietario';
    case Inquilino = 'inquilino';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Propietario => 'Propietario',
            self::Inquilino => 'Inquilino',
        };
    }

    /**
     * Check if the role is admin.
     */
    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Check if the role is propietario.
     */
    public function isPropietario(): bool
    {
        return $this === self::Propietario;
    }

    /**
     * Check if the role is inquilino.
     */
    public function isInquilino(): bool
    {
        return $this === self::Inquilino;
    }
}
