<?php

namespace App\Models;

use App\ExpenseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'concept_id',
        'type',
        'period',
        'due_date',
        'total_amount',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExpenseType::class,
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the building that owns this expense.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Get the concept for this expense.
     */
    public function concept(): BelongsTo
    {
        return $this->belongsTo(Concept::class);
    }

    /**
     * Get all expense details for this expense.
     */
    public function details(): HasMany
    {
        return $this->hasMany(ExpenseDetail::class);
    }

    /**
     * Get the total paid amount for this expense.
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->details()->sum('paid_amount');
    }

    /**
     * Get the total pending amount for this expense.
     */
    public function getTotalPendingAttribute(): float
    {
        return $this->total_amount - $this->total_paid;
    }

    /**
     * Check if the expense is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->total_pending <= 0;
    }
}
