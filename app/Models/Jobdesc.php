<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jobdesc extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'division_id',
        'title',
        'description',
        'level',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}