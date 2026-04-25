<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class AncSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = ['pregnancy_id', 'anc_number', 'schedule_date', 'schedule_time', 'note', 'status', 'created_by'];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'schedule_time' => 'datetime:H:i',
            'anc_number' => 'integer',
        ];
    }

    // Relationship
    public function preganancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class)->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function ancRecord(): HasOne
    {
        return $this->hasOne(AncRecord::class, 'schedule_id');
    }

    // Scopes

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', 'done');
    }

    public function scopeUpComing(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_date', '>=', today())
            ->orderBy('scheduled_date');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_date', '<', today());
    }

    // Helper
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }

    public function hasRecord(): bool
    {
        return $this->ancRecord()->exists();
    }

    public function markAsDone(): void
    {
        $this->update(['status' => 'done']);
    }
}
