<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nim',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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