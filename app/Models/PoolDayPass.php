<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PoolDayPass extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'date',
        'unit_id',
        'user_id',
        'resident_id',
        'guests_allowed',
        'used_at',
        'used_by_user_id',
        'used_pool_id',
        'used_guests_count',
        'pool_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'guests_allowed' => 'integer',
            'used_at' => 'datetime',
            'used_guests_count' => 'integer',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function usedPool(): BelongsTo
    {
        return $this->belongsTo(Pool::class, 'used_pool_id');
    }

    public function poolEntry(): BelongsTo
    {
        return $this->belongsTo(PoolEntry::class);
    }

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(PoolGuest::class, 'pool_day_pass_guests');
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
