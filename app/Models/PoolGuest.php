<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoolGuest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'created_by_user_id',
        'name',
        'birth_date',
        'profile_photo_path',
        'document_type',
        'document_number',
        'phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function isMinor(): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        return $this->birth_date->age < 18;
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function dayPasses(): BelongsToMany
    {
        return $this->belongsToMany(PoolDayPass::class, 'pool_day_pass_guests');
    }

    public function poolEntries(): BelongsToMany
    {
        return $this->belongsToMany(PoolEntry::class, 'pool_entry_guests');
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : null;
    }
}
