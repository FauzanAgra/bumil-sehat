<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAncValidationRequest;
use App\Models\AncRecord;
use App\Models\AncValidation;
use App\Models\DetectedCondition;
use App\Events\AncValidationCreated;
use Illuminate\Support\Facades\DB;

class ValidationController extends Controller
{
    public function show(AncRecord $ancRecord)
    {
        $user = auth()->user();

        abort_if(
            $ancRecord->mother->assigned_midwife_id !== $user->id,
            403
        );

        $ancRecord->load([
            'mother.user',
            'schedule',
            'detectedConditions',
            'usgImage',
            'validation',
        ]);

        return view('dashboard.midwife.validate-anc', compact(
            'user',
            'ancRecord'
        ));
    }

    public function validate(StoreAncValidationRequest $request, AncRecord $ancRecord)
    {
        $user = auth()->user();

        $validated = $request->validated();

        DB::transaction(function () use ($ancRecord, $user, $validated) {
            // Pessimistic lock — cegah race condition
            $ancRecord = AncRecord::query()
                ->lockForUpdate()
                ->with(['mother.activePregnancy.ttdStatus', 'detectedConditions',])
                ->findOrFail($ancRecord->id);

            abort_if($ancRecord->is_locked, 422, 'Data sudah dikunci.');

            abort_if($ancRecord->mother->assigned_midwife_id !== $user->id, 403, 'Anda tidak memiliki akses ke data ini.');

            // Proses kondisi
            if (!empty($validated['conditions'])) {
                foreach ($validated['conditions'] as $condData) {
                    $$condition = DetectedCondition::query()
                        ->where('id', $condData['id'])
                        ->where('anc_record_id', $ancRecord->id)
                        ->firstOrFail();

                    // Pastikan kondisi ini milik ancRecord yang sedang divalidasi
                    abort_if($condition->anc_record_id !== $ancRecord->id, 403);

                    if ($condData['action'] === 'confirm') {
                        $condition->confirm($user, $condData['notes'] ?? null);
                    } else {
                        $condition->reject($user, $condData['notes'] ?? null);
                    }
                }
            }

            AncValidation::lockedRecord($ancRecord, $user, $validated['notes'] ?? null);

            event(new AncValidationCreated($validation));
        });

        return redirect()
            ->route('midwife.patients.show', $ancRecord->mother_id)
            ->with('success', 'Data ANC berhasil divalidasi dan dikunci.');
    }
}
