<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoolRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pool_id',
        'max_guests_per_unit',
        'max_entries_per_day',
        'allow_guests',
        'only_owners',
        'valid_from',
        'valid_to',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allow_guests' => 'boolean',
            'only_owners' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    /**
     * Get the pool that owns this rule.
     */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    /**
     * Scope to get active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            });
    }

    /**
     * Check if this rule is currently active.
     */
    public function isActive(): bool
    {
        $now = now();

        return $this->valid_from <= $now
            && ($this->valid_to === null || $this->valid_to >= $now);
    }
}
