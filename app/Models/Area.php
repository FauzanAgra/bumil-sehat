<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Area extends Model
{
    protected $fillable = ['subdistrict_id', 'rw_number'];

    // Relationships
    public function subdistrict(): BelongsTo
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function midwifeAreas(): HasMany
    {
        return $this->hasMany(MidwifeArea::class);
    }

    public  function activeMidwifeArea(): HasOne
    {
        return $this->hasOne(MidwifeArea::class)->where('is_active', true);
    }

    public function motherProfiles(): HasMany
    {
        return $this->hasMany(MotherProfile::class);
    }

    public function getLabelAttribute(): string
    {
        return "RW {$this->rw_number} - {$this->subdistrict->name}";
    }
}
