<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialization extends Model
{
    protected $fillable = [
        'name',
        'role_group',
        'icon',
        'color',
        'wa_greeting',
        'is_active',
        'sort_order'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relations
    public function medicalProfiles(): HasMany
    {
        return  $this->hasMany(MedicalProfile::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeMidwife(Builder $query): Builder
    {
        return $query->where('role_group', 'midwife');
    }

    public function scopeDoctor(Builder $query): Builder
    {
        return $query->where('role_group', 'doctor');
    }

    // Helper
    public function isMidwifeGroup(): bool
    {
        return $this->role_group === 'midwife';
    }
}
