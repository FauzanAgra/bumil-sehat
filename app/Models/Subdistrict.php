<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subdistrict extends Model
{
    protected $fillable = ['name'];

    // Relationships
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function motherProfiles(): HasMany
    {
        return $this->hasMany(MotherProfile::class);
    }
}
