<?php

namespace App\Models;

use GuzzleHttp\Psr7\Query;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function Symfony\Component\Clock\now;

class DetectedCondition extends Model
{
    protected $fillable = [
        'anc_record_id',
        'condition_type',
        'status',
        'notes',
        'detected_at',
        'validated_by'
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'validated_at' => 'datetime'
        ];
    }

    // Label Condition for human-readable
    const CONDITION_LABELS = [
        'kek'          => 'Kekurangan Energi Kronik (KEK)',
        'preeclampsia' => 'Preeklampsia',
        'anemia'       => 'Anemia',
    ];

    const STATUS_LABELS = [
        'detected'  => 'Terdeteksi',
        'confirmed' => 'Dikonfirmasi',
        'rejected'  => 'Ditolak',
    ];

    // Relations
    public function ancRecord(): BelongsTo
    {
        return $this->belongsTo(AncRecord::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeDetected(Builder $query): Builder
    {
        return $query->where('status', 'detected');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePendingValidation(Builder $query): Builder
    {
        return $query->where('status', 'detected');
    }

    // Helper
    public function getLabelAttribute(): string
    {
        return self::CONDITION_LABELS($this->condition_type) ?? $this->condition_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS($this->status) ?? $this->status;
    }

    public function confirm(User $midwife, ?string $notes = null): void
    {
        $this->update([
            'status' => 'confirmed',
            'validated_by' => $midwife->id,
            'validated_at' => now(),
            'notes' => $notes
        ]);
    }

    public function reject(User $midwife, ?string $notes = null): void
    {
        $this->update([
            'status'       => 'rejected',
            'validated_by' => $midwife->id,
            'validated_at' => now(),
            'notes'        => $notes,
        ]);
    }
}
