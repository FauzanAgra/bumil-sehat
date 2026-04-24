<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotherProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nik_encrypted',
        'nik_hash',
        'birth_date',
        'address',
        'subdistrict_id',
        'area_id',
        'rt',
        'assigned_midwife_id',
    ];

    protected $hidden = ['nik_encrypted', 'nik_hash'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date'
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
