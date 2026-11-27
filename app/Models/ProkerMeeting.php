<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProkerMeeting extends Model
{
    use HasFactory;

    public const SCOPE_PLENO = 'pleno';

    public const SCOPE_KOOR = 'koor';

    public const SCOPE_DIVISION = 'division';

    protected $fillable = [
        'proker_id',
        'scope',
        'proker_division_id',
        'weekday',
        'starts_at',
        'place',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
        ];
    }

    public function proker(): BelongsTo
    {
        return $this->belongsTo(Proker::class);
    }

    public function prokerDivision(): BelongsTo
    {
        return $this->belongsTo(ProkerDivision::class);
    }
}
