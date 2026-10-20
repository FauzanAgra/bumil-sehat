<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AncRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'mother_id',
        'recorded_by',
        'weight',
        'height',
        'lila',
        'systolic',
        'diastolic',
        'hb',
        'tt_status',
        'fetal_position',
        'fetal_heart_rate',
        'is_locked',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'lila' => 'decimal:2',
            'hb' => 'decimal:1',
            'is_locked' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    // Relationships
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AncSchedule::class, 'schedule_id')->withTrashed();
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(MotherProfile::class, 'mother_id')->withTrashed();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }

    public function usgImage(): HasOne
    {
        return $this->hasOne(AncUsgImage::class);
    }

    public function detectedConditions(): HasMany
    {
        return $this->hasMany(DetectedCondition::class, 'anc_record_id');
    }

    public function validation(): HasOne
    {
        return $this->hasOne(AncValidation::class);
    }

    // Scopes
    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }

    public function scopePendingValidation(Builder $query): Builder
    {
        return $query->unlocked()->doesntHave('validation');
    }

    // Automatic Detection Conditions
    public function detectConditions(): void
    {
        // KEK: lila < 23.5 cm
        if ($this->lila !== null && $this->lila < 23.5) {
            $this->detectedConditions()->firstOrCreate(
                ['condition_type' => 'kek'],
                [
                    'status' => 'detected',
                    'detected_at' => now(),
                ]
            );
        }

        // Preeklampsia: systolic >= 140 OR diastolic >= 90
        if (($this->systolic !== null && $this->systolic >= 140) ||
            ($this->diastolic !== null && $this->diastolic >= 90)
        ) {
            $this->detectedConditions()->firstOrCreate(
                ['condition_type' => 'preeclampsia'],
                [
                    'status' => 'detected',
                    'detected_at' => now(),
                ]
            );
        }

        // Anemia: hb < 11 g/dL
        if ($this->hb !== null && $this->hb < 11.0) {
            $this->detectedConditions()->firstOrCreate(
                ['condition_type' => 'anemia'],
                [
                    'status' => 'detected',
                    'detected_at' => now(),
                ]
            );
        }
    }

    /**
     * Deteksi kondisi dengan menghormati keputusan bidan yang sudah ada.
     *
     * Kondisi yang sudah 'confirmed' atau 'rejected' oleh bidan TIDAK akan
     * dideteksi ulang — keputusan bidan bersifat final.
     * Hanya kondisi yang belum pernah diproses bidan yang akan dideteksi baru.
     *
     * Digunakan saat record di-UPDATE (data diubah oleh ibu sebelum dikunci).
     */
    public function detectConditionsIfNoFinalDecision(): void
    {
        // Ambil kondisi yang sudah ada keputusan final (confirmed/rejected)
        $finalConditions = $this->detectedConditions()
            ->whereIn('status', ['confirmed', 'rejected'])
            ->pluck('condition_type')
            ->toArray();

        // KEK
        if ($this->lila !== null && $this->lila < 23.5 && ! in_array('kek', $finalConditions)) {
            $this->detectedConditions()->firstOrCreate(
                ['condition_type' => 'kek'],
                ['status' => 'detected', 'detected_at' => now()]
            );
        }

        // Preeklampsia
        if ((($this->systolic !== null && $this->systolic >= 140) ||
                ($this->diastolic !== null && $this->diastolic >= 90))
            && ! in_array('preeclampsia', $finalConditions)
        ) {
            $this->detectedConditions()->firstOrCreate(
                ['condition_type' => 'preeclampsia'],
                ['status' => 'detected', 'detected_at' => now()]
            );
        }

        // Anemia
        if (
            $this->hb !== null && $this->hb < 11.0
            && ! in_array('anemia', $finalConditions)
        ) {
            $this->detectedConditions()->firstOrCreate(
                ['condition_type' => 'anemia'],
                ['status' => 'detected', 'detected_at' => now()]
            );
        }
    }

    // Helper
    public function isValidated(): bool
    {
        return $this->validation()->exists();
    }

    public function hasAnyCondition(): bool
    {
        return $this->detectedConditions()->exists();
    }

    public function getBmiAttribute(): ?float
    {
        if (! $this->weight || ! $this->height) {
            return null;
        }
        $heightM = $this->height / 100;

        return round($this->weight / ($heightM ** 2), 1);
    }

    // Boot
    protected static function booted(): void
    {
        // After created
        static::created(function (AncRecord $record) {
            $record->detectConditions();
            $record->schedule?->markAsDone();
        });

        static::updated(function (AncRecord $record) {
            // Jangan proses jika yang berubah hanya is_locked
            // (lock terjadi di ValidationController, bukan update data ANC)
            $dirtyKeys = array_keys($record->getDirty());
            if ($dirtyKeys === ['is_locked']) {
                return;
            }
            // Hanya hapus kondisi 'detected' yang belum disentuh bidan
            // Kondisi 'confirmed' dan 'rejected' tidak disentuh
            $record->detectedConditions()
                ->where('status', 'detected')
                ->delete();

            // Deteksi ulang — tapi jangan override kondisi yang sudah pernah ada
            $record->detectConditionsIfNoFinalDecision();
        });

        static::updating(function (AncRecord $record) {
            // Izinkan update is_locked = true (proses locking itu sendiri)
            $dirty = $record->getDirty();
            if ($record->getOriginal('is_locked') && ! (count($dirty) === 1 && isset($dirty['is_locked']))) {
                throw new \LogicException(
                    "ANC Record #{$record->id} sudah dikunci dan tidak dapat diubah."
                );
            }
        });

        static::deleting(function (AncRecord $record) {
            if ($record->is_locked) {
                throw new \LogicException(
                    "ANC Record #{$record->id} sudah divalidasi dan tidak dapat dihapus."
                );
            }
        });
    }
}
