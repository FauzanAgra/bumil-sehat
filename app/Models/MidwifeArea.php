<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MidwifeArea extends Model
{
    protected $fillable = [
        'midwife_id',
        'area_id',
        'is_active',
        'assigned_at',
    ];

    protected function casts()
    {
        return [
            'is_active' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    // Relations
    public function midwife(): BelongsTo
    {
        return $this->belongsTo(User::class, 'midwife_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForMidwife(Builder $query, int $midwifeId): Builder
    {
        return $query->where('midwife_id', $midwifeId);
    }
}
