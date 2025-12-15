<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'number',
        'floor',
        'coefficient',
        'rooms',
        'terrazas',
        'area',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:4',
            'area' => 'decimal:2',
        ];
    }

    /**
     * Get the building that owns this unit.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Get all unit-user relationships for this unit.
     */
    public function unitUsers(): HasMany
    {
        return $this->hasMany(UnitUser::class);
    }

    /**
     * Get current users for this unit.
     */
    public function currentUsers()
    {
        return $this->hasMany(UnitUser::class)
            ->whereNull('ended_at')
            ->whereNull('deleted_at');
    }

    /**
     * Get the current owner (propietario) for this unit.
     */
    public function currentOwner()
    {
        return $this->hasOne(UnitUser::class)
            ->where('is_owner', true)
            ->whereNull('ended_at')
            ->whereNull('deleted_at')
            ->latest();
    }

    /**
     * Get the current responsible user for payment for this unit.
     */
    public function currentResponsible()
    {
        return $this->hasOne(UnitUser::class)
            ->where('is_responsible', true)
            ->whereNull('ended_at')
            ->whereNull('deleted_at')
            ->latest();
    }

    /**
     * Get all expense details for this unit.
     */
    public function expenseDetails(): HasMany
    {
        return $this->hasMany(ExpenseDetail::class);
    }

    /**
     * Get all pool entries for this unit.
     */
    public function poolEntries(): HasMany
    {
        return $this->hasMany(PoolEntry::class);
    }

    /**
     * Get all residents for this unit.
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    /**
     * Get active residents for this unit.
     * Active means: no ended_at date OR ended_at is in the future.
     */
    public function activeResidents(): HasMany
    {
        return $this->hasMany(Resident::class)
            ->where(function ($query) {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', now());
            })
            ->whereNull('deleted_at');
    }

    /**
     * Get full unit identifier (building name - unit number).
     */
    public function getFullIdentifierAttribute(): string
    {
        if (! $this->building) {
            return "Unidad #{$this->number}";
        }

        return "{$this->building->name} - {$this->number}";
    }
}
