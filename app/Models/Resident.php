<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'user_id',
        'name',
        'profile_photo_path',
        'document_type',
        'document_number',
        'birth_date',
        'relationship',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    /**
     * Get the unit this resident belongs to.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the responsible user (parent/tutor) for this resident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all pool entries for this resident.
     */
    public function poolEntries(): HasMany
    {
        return $this->hasMany(PoolEntry::class);
    }

    /**
     * Check if the resident is a minor (under 18 years old).
     */
    public function isMinor(): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        return $this->birth_date->age < 18;
    }

    /**
     * Get the resident's age.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : null;
    }

    /**
     * Scope to get active residents.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }
}
