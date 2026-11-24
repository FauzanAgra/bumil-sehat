<?php

namespace App\Listeners;

use App\Events\AncValidationCreated;
use App\Models\Pregnancy;
use App\Models\TtdStatus;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class AutoActivateTtdOnValidation
{
    public function handle(AncValidationCreated $event): void
    {
        $pregnancyId = $event->validation
            ->ancRecord()
            ->with('schedule.pregnancy')
            ->first()
            ?->schedule
            ?->pregnancy
            ?->id;

        if (! $pregnancyId) {
            return;
        }

        DB::transaction(function () use ($pregnancyId): void {
            // Lock baris kehamilan target untuk mencegah race condition dengan toggle manual Bidan (SEC-006, SEC-011)
            $pregnancy = Pregnancy::lockForUpdate()->find($pregnancyId);

            if (! $pregnancy || ! $pregnancy->isActive() || $pregnancy->gestational_week < 10) {
                return;
            }

            $ttd = TtdStatus::where('pregnancy_id', $pregnancy->id)
                ->lockForUpdate()
                ->first();

            if (! $ttd) {
                try {
                    $pregnancy->ttdStatus()->create([
                        'is_active' => true,
                        'started_at' => now(),
                        'activated_by' => 'system',
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $existing = TtdStatus::where('pregnancy_id', $pregnancy->id)
                        ->lockForUpdate()
                        ->first();

                    if ($existing && ! $existing->is_active) {
                        $existing->activate('system');
                    }
                }
            } elseif (! $ttd->is_active) {
                $ttd->activate('system');
            }
        });
    }
}
