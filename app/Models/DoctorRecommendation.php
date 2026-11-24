<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorRecommendation extends Model
{
    public const LEVEL_ROUTINE = 'routine';

    public const LEVEL_MONITOR = 'monitor';

    public const LEVEL_REFER = 'refer';

    protected $fillable = [
        'pregnancy_id',
        'anc_record_id',
        'doctor_id',
        'follow_up_level',
        'recommendation',
    ];

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class)->withTrashed();
    }

    public function ancRecord(): BelongsTo
    {
        return $this->belongsTo(AncRecord::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id')->withTrashed();
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->follow_up_level) {
            self::LEVEL_MONITOR => 'Perlu Pemantauan',
            self::LEVEL_REFER => 'Rujukan Faskes Lanjutan',
            default => 'Kontrol Rutin',
        };
    }

    public function getLevelBadgeClassAttribute(): string
    {
        return match ($this->follow_up_level) {
            self::LEVEL_MONITOR => 'bg-amber-50 text-amber-700 border border-amber-200',
            self::LEVEL_REFER => 'bg-red-50 text-red-700 border border-red-200',
            default => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        };
    }

    public function getDoctorDisplayNameAttribute(): string
    {
        $doctor = $this->doctor;
        if (! $doctor) {
            return 'Tenaga Kesehatan';
        }

        $profile = $doctor->medicalProfile;
        $prefix = $profile?->degree_prefix ? trim($profile->degree_prefix).' ' : '';
        $suffix = $profile?->degree_suffix ? ', '.trim($profile->degree_suffix) : '';

        return trim($prefix.$doctor->name.$suffix);
    }

    public function getDoctorSpecializationAttribute(): string
    {
        return $this->doctor?->medicalProfile?->specialization?->name ?? 'Dokter';
    }
}
