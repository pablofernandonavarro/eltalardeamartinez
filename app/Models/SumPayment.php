<?php

namespace App\Models;

use App\Enums\SumPaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SumPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'amount',
        'status',
        'payment_method',
        'transaction_reference',
        'notes',
        'paid_at',
        'paid_by',
        'mp_preference_id',
        'mp_payment_id',
        'mp_status',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'status'  => SumPaymentStatus::class,
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
        return $query->where('status', SumPaymentStatus::Pending);
    }

    public function scopePaid($query)
    {
        return $query->where('status', SumPaymentStatus::Paid);
    }

    public function markAsPaid(string $paymentMethod, ?string $transactionRef = null, ?string $notes = null): void
    {
        $this->update([
            'status'                => SumPaymentStatus::Paid,
            'payment_method'        => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'notes'                 => $notes,
            'paid_at'               => now(),
            'paid_by'               => auth()->id(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => SumPaymentStatus::Cancelled]);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if (! $this->payment_method && $this->mp_payment_id) {
            return 'Mercado Pago';
        }

        if (! $this->payment_method) {
            return '-';
        }

        return match ($this->payment_method) {
            'cash'     => 'Efectivo',
            'transfer' => 'Transferencia',
            'card'     => 'Tarjeta',
            'online'   => 'Mercado Pago',
            default    => $this->payment_method,
        };
    }
}
