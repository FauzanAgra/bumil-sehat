<?php

namespace App\Listeners;

use App\Events\AncValidationCreated;

class AutoActivateTtdOnValidation
{
    public function handle(AncValidationCreated $event): void
    {
        $pregnancy = $event->validation->ancRecord->mother->activePregnancy;
        if (!$pregnancy || $pregnancy->gestational_week < 10) return;

        $ttd = $pregnancy->ttdStatus;
        if (!$ttd) {
            $pregnancy->ttdStatus()->create([
                'is_active' => true,
                'started_at' => now(),
                'activated_by' => 'system',
            ]);
        } elseif (!$ttd->is_active) {
            $ttd->activate('system');
        }
    }
}
