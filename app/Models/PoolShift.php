<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoolShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'user_id',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Relación con la pileta
     */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    /**
     * Relación con el bañero
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para turnos activos (sin fecha de fin)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Verificar si el turno está activo
     */
    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * Finalizar el turno
     */
    public function end(?string $notes = null): void
    {
        $this->update([
            'ended_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Obtener turno activo de un bañero
     */
    public static function getActiveShiftForUser(int $userId): ?self
    {
        return self::query()
            ->where('user_id', $userId)
            ->active()
            ->first();
    }

    /**
     * Obtener bañero activo de una pileta
     */
    public static function getActiveShiftForPool(int $poolId): ?self
    {
        return self::query()
            ->where('pool_id', $poolId)
            ->active()
            ->first();
    }

    /**
     * Verificar si un bañero puede iniciar turno
     */
    public static function canStartShift(int $userId): bool
    {
        // No puede iniciar si ya tiene un turno activo
        return ! self::query()
            ->where('user_id', $userId)
            ->active()
            ->exists();
    }
}
