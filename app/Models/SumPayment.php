<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'amount',
        'status',
        'payment_method',
        'transaction_reference',
        'notes',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Relación con la reserva
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(SumReservation::class, 'reservation_id');
    }

    /**
     * Relación con el usuario que registró el pago
     */
    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para pagos confirmados
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Marcar pago como pagado
     */
    public function markAsPaid(string $paymentMethod, ?string $transactionRef = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'notes' => $notes,
            'paid_at' => now(),
            'paid_by' => auth()->id(),
        ]);
    }

    /**
     * Marcar pago como cancelado
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Obtener el label del estado
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Obtener el label del método de pago
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        if (! $this->payment_method) {
            return '-';
        }

        return match ($this->payment_method) {
            'cash' => 'Efectivo',
            'transfer' => 'Transferencia',
            'card' => 'Tarjeta',
            'online' => 'Pago Online',
            default => $this->payment_method,
        };
    }
}
