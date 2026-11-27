<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'nim',
        'phone',
        'password',
        'activated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function advisedOrganisations(): HasMany
    {
        return $this->hasMany(Organisation::class, 'advisor_user_id');
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null && $this->password !== null;
    }

    public function isAdvisor(): bool
    {
        return $this->advisedOrganisations()->exists();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership(): ?Membership
    {
        return $this->memberships()
            ->where('status', 'active')
            ->whereHas('term', fn ($q) => $q->where('is_active', true))
            ->with(['division', 'jobdesc', 'organisation', 'term'])
            ->first();
    }

    public function isHod(): bool
    {
        return $this->activeMembership()?->jobdesc?->level === 'hod';
    }
}
