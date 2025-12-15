<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'user_id',
        'is_owner',
        'is_responsible',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'is_responsible' => 'boolean',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    /**
     * Get the unit that owns this relationship.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user that owns this relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active relationships.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope to get owner relationships.
     */
    public function scopeOwner($query)
    {
        return $query->where('is_owner', true);
    }

    /**
     * Scope to get responsible relationships.
     */
    public function scopeResponsible($query)
    {
        return $query->where('is_responsible', true);
    }
}
