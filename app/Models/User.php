<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'region_id',
        'notes',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isMusician(): bool
    {
        return $this->role === UserRole::Musician;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && $this->is_active;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function instruments(): BelongsToMany
    {
        return $this->belongsToMany(Instrument::class)->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(GigAssignment::class);
    }

    public function gigs(): HasManyThrough
    {
        return $this->hasManyThrough(
            Gig::class,
            GigAssignment::class,
            'user_id',
            'id',
            'id',
            'gig_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMusicians(Builder $query): Builder
    {
        return $query->where('role', UserRole::Musician);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', UserRole::Admin);
    }
}
