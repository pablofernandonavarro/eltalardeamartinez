<?php

namespace App\Models;

use App\ExpenseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_id',
        'unit_id',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'status' => ExpenseStatus::class,
            'paid_at' => 'date',
        ];
    }

    /**
     * Get the expense that owns this detail.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the unit that owns this detail.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get all payments for this expense detail.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the pending amount for this detail.
     */
    public function getPendingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * Check if the detail is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->pending_amount <= 0;
    }

    /**
     * Update the status based on paid amount.
     */
    public function updateStatus(): void
    {
        if ($this->isFullyPaid()) {
            $this->status = ExpenseStatus::Pagada;
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = ExpenseStatus::Parcial;
        } else {
            $this->status = ExpenseStatus::Pendiente;
            $this->paid_at = null;
        }

        $this->save();
    }
}
