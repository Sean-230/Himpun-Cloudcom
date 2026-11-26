<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'faculty',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function activeTerm()
    {
        return $this->hasOne(Term::class)->where('is_active', true);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    public function jobdescs(): HasMany
    {
        return $this->hasMany(Jobdesc::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}