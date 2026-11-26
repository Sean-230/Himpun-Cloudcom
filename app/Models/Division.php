<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'name',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
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