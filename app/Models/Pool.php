<?php

namespace App\Models;

use App\PoolStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => PoolStatus::class,
        ];
    }

    /**
     * Get all rules for this pool.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(PoolRule::class);
    }

    /**
     * Get the current active rule for this pool.
     */
    public function currentRule()
    {
        return $this->hasOne(PoolRule::class)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->latest('valid_from');
    }

    /**
     * Get all entries for this pool.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PoolEntry::class);
    }

    /**
     * Check if the pool is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->status === PoolStatus::Habilitada;
    }
}
