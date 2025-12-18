<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complex_id',
        'name',
        'address',
        'floors',
        'notes',
    ];

    /**
     * Get the complex that owns this building.
     */
    public function complex(): BelongsTo
    {
        return $this->belongsTo(Complex::class);
    }

    /**
     * Get all units for this building.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get all expenses for this building.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    
}
