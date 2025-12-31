<?php

namespace App;

enum Role: string
{
    case Admin = 'admin';
    case Banero = 'banero';
    case Propietario = 'propietario';
    case Inquilino = 'inquilino';
    case Residente = 'residente';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Banero => 'Bañero',
            self::Propietario => 'Propietario',
            self::Inquilino => 'Inquilino',
            self::Residente => 'Residente',
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
     * Check if the role is banero.
     */
    public function isBanero(): bool
    {
        return $this === self::Banero;
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

    /**
     * Check if the role is residente.
     */
    public function isResidente(): bool
    {
        return $this === self::Residente;
    }
}
