<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProkerAssignment extends Model
{
    use HasFactory;

    public const ROLE_KETUPEL = 'ketupel';

    public const ROLE_PJ = 'pj';

    public const ROLE_HOD = 'hod';

    public const ROLE_KOORDINATOR = 'koordinator';

    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'proker_id',
        'membership_id',
        'proker_division_id',
        'role',
        'reports_to_id',
    ];

    public function proker(): BelongsTo
    {
        return $this->belongsTo(Proker::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function prokerDivision(): BelongsTo
    {
        return $this->belongsTo(ProkerDivision::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'reports_to_id');
    }
}
