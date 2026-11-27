<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProkerDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'proker_id',
        'division_id',
        'name',
    ];

    public function proker(): BelongsTo
    {
        return $this->belongsTo(Proker::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProkerAssignment::class);
    }

    public function jobdescs(): HasMany
    {
        return $this->hasMany(ProkerDivisionJobdesc::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ProkerMeeting::class);
    }
}
