<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TtdStatus extends Model
{
    protected $fillable = [
        'pregnancy_id',
        'is_active',
        'started_at',
        'activated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'started_at' => 'datetime',
        ];
    }

    // Relations
    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TtdLog::class)->orderBy('date');
    }

    // Helper
    // Berapa hari sudah diambil dalam bulan ini
    public function getTakenThisMonthAttribute(): int
    {
        return $this->logs()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('is_taken', true)
            ->count();
    }

    // Berapa hari total sudah diambil
    public function getTotalTakenAttribute(): int
    {
        return $this->logs()->where('is_taken', true)->count();
    }

    // Apakah hari ini sudah diambil
    public function isTakenToday(): bool
    {
        return $this->logs()
            ->where('date', today())
            ->where('is_taken', true)
            ->exists();
    }

    // Aktifkan TTD
    public function activate(string $activatedBy = 'system'): void
    {
        $this->update([
            'is_active'    => true,
            'started_at'   => now(),
            'activated_by' => $activatedBy,
        ]);
    }
}
