<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialization_id',
        'license_number',
        'degree_prefix',
        'degree_suffix',
        'phone',
        'availability'
    ];

    // Relation
    public function user(): BelongsTo
    {
        // withTrashed agar profil ibu tetap bisa diakses meski user di-softdelete
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
        // withTrashed agar tetap muncul meski bidan di-softdelete
        return $this->belongsTo(User::class, 'assigned_midwife_id')->withTrashed();
    }

    public function pregnancies(): HasMany
    {
        return $this->hasMany(Pregnancy::class, 'mother_id');
    }

    public function activePregnancy(): HasOne
    {
        return $this->hasOne(Pregnancy::class, 'mother_id')
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function ancRecords(): HasMany
    {
        return $this->hasMany(AncRecord::class, 'mother_id');
    }

    // Helper

    // Full Name with degree
    public function getFullNameAttribute(): string
    {
        $prefix = $this->degree_prefix ? "{$this->degree_prefix}" : '';

        $suffix = $this->degree_suffix ? ", {$this->degree_suffix}" : '';
        return "{$prefix}{$this->user->name}{$suffix}";
    }

    // Number Whatsapp
    public function getWhatsappNumberAttribute(): string
    {
        return $this->phone ?? $this->user->phone;
    }
}
