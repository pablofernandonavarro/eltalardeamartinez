<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'role',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * Get all unit-user relationships for this user.
     */
    public function unitUsers(): HasMany
    {
        return $this->hasMany(UnitUser::class);
    }

    /**
     * Get current unit-user relationships for this user.
     */
    public function currentUnitUsers()
    {
        return $this->hasMany(UnitUser::class)
            ->whereNull('ended_at')
            ->whereNull('deleted_at');
    }

    /**
     * Get all payments made by this user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all pool entries for this user.
     */
    public function poolEntries(): HasMany
    {
        return $this->hasMany(PoolEntry::class);
    }

    /**
     * Get all residents for which this user is responsible.
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /**
     * Check if the user is a banero.
     */
    public function isBanero(): bool
    {
        return $this->role === Role::Banero;
    }

    /**
     * Check if the user is a propietario.
     */
    public function isPropietario(): bool
    {
        return $this->role === Role::Propietario;
    }

    /**
     * Check if the user is an inquilino.
     */
    public function isInquilino(): bool
    {
        return $this->role === Role::Inquilino;
    }

    /**
     * Check if the user has a role assigned.
     */
    public function hasRole(): bool
    {
        return $this->role !== null;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the URL to the user's profile photo.
     */
    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : null;
    }

    /**
     * Check if the user is approved.
     */
    public function isApproved(): bool
    {
        // Admins are always approved
        if ($this->isAdmin()) {
            return true;
        }

        return $this->approved_at !== null;
    }

    /**
     * Approve the user.
     */
    public function approve(): void
    {
        $this->update(['approved_at' => now()]);
    }

    /**
     * Reject/unapprove the user.
     */
    public function reject(): void
    {
        $this->update(['approved_at' => null]);
    }
}
