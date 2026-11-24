<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
    public function pregnancy(): BelongsTo
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
            ->where('schedule_date', '>=', today())
            ->orderBy('schedule_date');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('schedule_date', '<', today());
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

    public function isReminderDue(): bool
    {
        if (! $this->isScheduled() || ! $this->schedule_date) {
            return false;
        }

        return $this->schedule_date->isToday() || $this->schedule_date->isTomorrow();
    }

    public function getReminderLabelAttribute(): ?string
    {
        if (! $this->isReminderDue()) {
            return null;
        }

        return $this->schedule_date->isToday()
            ? 'Kontrol Hari Ini'
            : 'Kontrol Besok (H-1)';
    }

    public function buildWhatsAppReminderUrl(): ?string
    {
        $motherUser = $this->pregnancy?->mother?->user;
        $rawPhone = $motherUser?->phone;

        if (! $rawPhone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $rawPhone);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $waPhone = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            $waPhone = $digits;
        } else {
            $waPhone = '62'.$digits;
        }

        $date = $this->schedule_date;
        if ($date?->isToday()) {
            $dateText = 'hari ini ('.$date->translatedFormat('d F Y').')';
        } elseif ($date?->isTomorrow()) {
            $dateText = 'besok ('.$date->translatedFormat('d F Y').')';
        } else {
            $dateText = 'pada '.($date ? $date->translatedFormat('l, d F Y') : '-');
        }

        $timeText = $this->schedule_time
            ? ' pukul '.Carbon::parse($this->schedule_time)->format('H:i').' WIB'
            : '';

        $noteText = $this->note ? ' ('.$this->note.')' : '';

        $message = "Halo Bunda {$motherUser->name}, mengingatkan jadwal kontrol ANC ke-{$this->anc_number} {$dateText}{$timeText}{$noteText}. Jangan lupa membawa buku KIA ya, Bunda. Terima kasih.";

        return 'https://wa.me/'.$waPhone.'?text='.rawurlencode($message);
    }
}
