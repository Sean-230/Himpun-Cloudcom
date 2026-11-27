<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProkerDivisionJobdesc extends Model
{
    use HasFactory;

    protected $fillable = [
        'proker_division_id',
        'title',
        'days_before',
        'done',
    ];

    protected function casts(): array
    {
        return [
            'days_before' => 'integer',
            'done' => 'boolean',
        ];
    }

    public function prokerDivision(): BelongsTo
    {
        return $this->belongsTo(ProkerDivision::class);
    }
}
