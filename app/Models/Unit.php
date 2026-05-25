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
        'uf_code',
        'floor',
        'coefficient',
        'rooms',
        'terrazas',
        'area',
        'max_residents',
        'has_pets',
        'notes',
        'owner',
    ];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:4',
            'area' => 'decimal:2',
            'has_pets' => 'boolean',
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
     * Get pets for this unit.
     */
    public function pets(): HasMany
    {
        return $this->hasMany(UnitPet::class);
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
     * Returns the floor label, deriving it from the unit number when the field is empty.
     * Format: [building][floor][apt] — the second-to-last digit is always the floor.
     * e.g. "701" → PB, "711" → 1, "722" → 2
     */
    public function getFloorLabelAttribute(): string
    {
        if ($this->floor) {
            return $this->floor;
        }

        $number = (string) $this->number;

        if (str_starts_with(strtoupper($number), 'PB')) {
            return 'PB';
        }

        if (is_numeric($number) && strlen($number) >= 3) {
            $floorInt = (int) substr($number, -2, 1);
            return $floorInt === 0 ? 'PB' : (string) $floorInt;
        }

        return '-';
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
