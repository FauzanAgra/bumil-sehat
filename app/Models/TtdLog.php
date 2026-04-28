<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TtdLog extends Model
{
    protected $fillable = [
        'ttd_status_id',
        'date',
        'is_taken',
    ];

    protected function casts(): array
    {
        return [
            'date'     => 'date',
            'is_taken' => 'boolean',
        ];
    }

    // Relations
    public function ttdStatus(): BelongsTo
    {
        return $this->belongsTo(TtdStatus::class);
    }

    // Scopes
    public function scopeTaken(Builder $query): Builder
    {
        return $query->where('is_taken', true);
    }

    public function scopeMissed(Builder $query): Builder
    {
        return $query->where('is_taken', false)
            ->where('date', '<', today());
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }
}
