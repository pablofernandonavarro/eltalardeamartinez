<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoolEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pool_id',
        'unit_id',
        'user_id',
        'resident_id',
        'guests_count',
        'entered_at',
        'exited_at',
        'exited_by_user_id',
        'notes',
        'exit_notes',
    ];

    protected function casts(): array
    {
        return [
            'guests_count' => 'integer',
            'entered_at' => 'datetime',
            'exited_at' => 'datetime',
        ];
    }

    /**
     * Get the pool for this entry.
     */
    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    /**
     * Get the unit for this entry.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user for this entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the resident for this entry.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function exitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exited_by_user_id');
    }

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(PoolGuest::class, 'pool_entry_guests');
    }

    /**
     * Scope to get entries for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('entered_at', $date);
    }

    /**
     * Scope to get entries for a specific unit.
     */
    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}
