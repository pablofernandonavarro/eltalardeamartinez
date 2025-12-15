<?php

namespace App\Models;

use App\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_detail_id',
        'user_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'status' => PaymentStatus::class,
        ];
    }

    /**
     * Get the expense detail that owns this payment.
     */
    public function expenseDetail(): BelongsTo
    {
        return $this->belongsTo(ExpenseDetail::class);
    }

    /**
     * Get the user that made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get processed payments.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', PaymentStatus::Procesado);
    }
}
