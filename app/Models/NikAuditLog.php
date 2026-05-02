<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NikAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mother_profile_id',
        'action',
        'actor_id',
        'actor_role',
        'ip_address',
        'user_agent',
        'metadata',
        'is_anomaly',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'is_anomaly' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    const ACTION_READ    = 'READ';
    const ACTION_DECRYPT = 'DECRYPT';
    const ACTION_WRITE   = 'WRITE';
    const ACTION_UPDATE  = 'UPDATE';
    const ACTION_DELETE  = 'DELETE';
    const ACTION_FAILED  = 'FAILED';

    // Relations
    public function motherProfile(): BelongsTo
    {
        return $this->belongsTo(MotherProfile::class)->withTrashed();
    }

    // Factory Method

    public static function record(
        int    $motherProfileId,
        string $action,
        array  $metadata   = [],
        bool   $isAnomaly  = false,
    ): static {
        $user = auth()->user();

        return static::create([
            'mother_profile_id' => $motherProfileId,
            'action'            => $action,
            'actor_id'          => $user?->id,
            'actor_role'        => $user?->role ?? 'system',
            'ip_address'        => request()->ip() ?? '0.0.0.0',
            'user_agent'        => request()->userAgent(),
            'metadata'          => $metadata,
            'is_anomaly'        => $isAnomaly,
            'created_at'        => now(),
        ]);
    }

    // Block Update & Delete (Immutable)

    protected static function booted(): void
    {
        static::updating(fn() => throw new \LogicException(
            'NikAuditLog bersifat immutable dan tidak dapat diubah.'
        ));

        static::deleting(fn() => throw new \LogicException(
            'NikAuditLog tidak dapat dihapus sesuai ketentuan UU PDP.'
        ));
    }
}
