<?php

namespace App\Models;

use App\Services\NIKService;
use App\Traits\HasNIKEncryption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotherProfile extends Model
{
    use SoftDeletes, HasNIKEncryption;

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

    public function subdistrict(): BelongsTo
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function assignedMidwife(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_midwife_id')->withTrashed();
    }

    public function pregnancies(): HasMany
    {
        return $this->hasMany(Pregnancy::class, 'mother_id');
    }

    public function activePregnancy(): HasOne
    {
        return $this->hasOne(Pregnancy::class, 'mother_id')->where('status', 'active')->latestOfMany();
    }

    public function ancRecords(): HasMany
    {
        return $this->hasMany(AncRecord::class, 'mother_id');
    }

    // Scopes
    public function scopeByNik(Builder $query, string $nik): Builder
    {
        $hash = app(NIKService::class)->hashNIK($nik);
        return $query->where('nik_hash', $hash);
    }

    public function scopeAssignedTo(Builder $query, int $midwifeId): Builder
    {
        return $query->where('assigned_midwife_id', $midwifeId);
    }

    // Helper
    public function getAgeAttribute(): int
    {
        return $this->birth_date->age;
    }
}
