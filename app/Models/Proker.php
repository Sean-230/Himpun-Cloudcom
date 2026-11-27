<?php

namespace App\Models;

use App\Enums\ProkerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Proker extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => ProkerStatus::Usulan->value,
    ];

    protected $fillable = [
        'organisation_id',
        'term_id',
        'previous_proker_id',
        'name',
        'type',
        'status',
        'description',
        'starts_on',
        'ends_on',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProkerStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function previousProker(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_proker_id');
    }

    public function followUpProkers(): HasMany
    {
        return $this->hasMany(self::class, 'previous_proker_id');
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(ProkerDivision::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProkerAssignment::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ProkerMeeting::class);
    }

    public function jobdescs(): HasManyThrough
    {
        return $this->hasManyThrough(ProkerDivisionJobdesc::class, ProkerDivision::class);
    }
}
