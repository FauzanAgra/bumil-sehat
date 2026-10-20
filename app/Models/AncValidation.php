<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AncValidation extends Model
{
    protected $fillable = [
        'anc_record_id',
        'midwife_id',
        'notes',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
        ];
    }

    // Relationship
    public function ancRecord(): BelongsTo
    {
        return $this->belongsTo(AncRecord::class);
    }

    public function midwife(): BelongsTo
    {
        return $this->belongsTo(User::class, 'midwife_id');
    }

    public static function lockedRecord(AncRecord $record, User $midwife, ?string $notes = null): static
    {
        $validation = static::create([
            'anc_record_id' => $record->id,
            'midwife_id' => $midwife->id,
            'notes' => $notes,
            'locked_at' => now(),
        ]);

        $record->update(['is_locked' => true]);

        return $validation;
    }
}
