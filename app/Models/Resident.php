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
        'auth_user_id',
        'name',
        'email',
        'profile_photo_path',
        'document_type',
        'document_number',
        'qr_token',
        'invitation_token',
        'invitation_sent_at',
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
            'invitation_sent_at' => 'datetime',
        ];
    }

    /**
     * Set the resident's name (normalized).
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = $this->normalizeName($value);
    }

    /**
     * Normalize name: trim, collapse spaces, title case.
     */
    protected function normalizeName(string $name): string
    {
        // Trim espacios
        $name = trim($name);
        
        // Reemplazar múltiples espacios por uno solo
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Convertir a title case (primera letra de cada palabra en mayúscula)
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
        
        return $name;
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
     * Get the authenticated user account for this resident (if they have one).
     */
    public function authUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auth_user_id');
    }

    /**
     * Get all pool entries for this resident.
     */
    public function poolEntries(): HasMany
    {
        return $this->hasMany(PoolEntry::class);
    }

    /**
     * Check if the resident is a minor (under 15 years old for QR purposes).
     */
    public function isMinor(): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        return $this->birth_date->age < 15;
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

    /**
     * Generate a unique QR token for this resident (only if 15+ years old and has auth account).
     */
    public function generateQrToken(): void
    {
        if (! $this->canHavePersonalQr()) {
            return;
        }

        $this->qr_token = (string) \Illuminate\Support\Str::uuid();
        $this->save();
    }

    /**
     * Check if resident can have a personal QR (must be 15+ and have auth account).
     */
    public function canHavePersonalQr(): bool
    {
        return ! $this->isMinor() && $this->auth_user_id !== null;
    }

    /**
     * Check if resident is eligible to be invited (15+ and no auth account yet).
     */
    public function canBeInvited(): bool
    {
        return ! $this->isMinor() && $this->auth_user_id === null;
    }

    /**
     * Check if this resident has their own auth account.
     */
    public function hasAuthAccount(): bool
    {
        return $this->auth_user_id !== null;
    }

    /**
     * Generate invitation token for this resident.
     */
    public function generateInvitationToken(): string
    {
        $this->invitation_token = \Illuminate\Support\Str::random(32);
        $this->invitation_sent_at = now();
        $this->save();

        return $this->invitation_token;
    }

    /**
     * Get invitation URL.
     */
    public function getInvitationUrl(): ?string
    {
        if (! $this->invitation_token) {
            return null;
        }

        return route('resident.accept-invitation', ['token' => $this->invitation_token]);
    }
}
