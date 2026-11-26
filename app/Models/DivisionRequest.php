<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionRequest extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'Menunggu';
    public const STATUS_DIPROSES = 'Diproses';
    public const STATUS_SELESAI  = 'Selesai';
    public const STATUS_DITOLAK  = 'Ditolak';

    public const STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_DIPROSES,
        self::STATUS_SELESAI,
        self::STATUS_DITOLAK,
    ];

    protected $fillable = [
        'proker_id',
        'from_proker_division_id',
        'to_proker_division_id',
        'from_user_id',
        'to_user_id',
        'title',
        'note',
        'due_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
        ];
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    #public function proker(): BelongsTo
    #{
    #    return $this->belongsTo(\App\Models\Proker::class, 'proker_id');
    #}

    #public function fromDivision(): BelongsTo
    #{
    #    return $this->belongsTo(\App\Models\ProkerDivision::class, 'from_proker_division_id');}

    #public function toDivision(): BelongsTo
    #{
    #    return $this->belongsTo(\App\Models\ProkerDivision::class, 'to_proker_division_id');
    #}

    public function canTransitionTo(string $nextStatus): bool
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => in_array($nextStatus, [self::STATUS_DIPROSES, self::STATUS_DITOLAK], true),
            self::STATUS_DIPROSES => $nextStatus === self::STATUS_SELESAI,
            default => false,
        };
    }
}