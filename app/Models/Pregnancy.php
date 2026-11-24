<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pregnancy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mother_id',
        'hpht_date',
        'hpl_date',
        'status',
        'completed_at',
        'completed_by',
        'completion_note',
    ];

    protected function casts(): array
    {
        return [
            'hpht_date' => 'date',
            'hpl_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    // Relations
    public function mother(): BelongsTo
    {
        return $this->belongsTo(MotherProfile::class, 'mother_id')->withTrashed();
    }

    public function ancSchedules(): HasMany
    {
        return $this->hasMany(AncSchedule::class)->orderBy('anc_number');
    }

    public function ttdStatus(): HasOne
    {
        return $this->hasOne(TtdStatus::class);
    }

    public function nextSchedule(): HasOne
    {
        return $this->hasOne(AncSchedule::class)
            ->where('status', 'scheduled')
            ->orderBy('schedule_date')
            ->ofMany('schedule_date', 'min');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by')->withTrashed();
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(DoctorRecommendation::class)->latest();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    // Helper
    public function getGestationalWeekAttribute(): int
    {
        $referenceDate = ($this->status === 'completed' && $this->completed_at !== null)
            ? $this->completed_at
            : now();

        return (int) max(0, floor($this->hpht_date->diffInWeeks($referenceDate)));
    }

    public function getTrimesterAttribute(): int
    {
        return match (true) {
            $this->gestational_week <= 12 => 1,
            $this->gestational_week <= 27 => 2,
            default => 3
        };
    }

    public function getCompletedAncCountAttribute(): int
    {
        // Kalau relasi sudah di-eager load, gunakan dari cache
        if ($this->relationLoaded('ancSchedules')) {
            return $this->ancSchedules->where('status', 'done')->count();
        }

        // Fallback ke query
        return $this->ancSchedules()->where('status', 'done')->count();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function complete(User $midwife, ?string $note = null): void
    {
        $cleanNote = $note !== null && trim($note) !== '' ? trim($note) : null;

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $midwife->id,
            'completion_note' => $cleanNote,
        ]);

        $this->ttdStatus()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->unsetRelation('ttdStatus');
    }
}
