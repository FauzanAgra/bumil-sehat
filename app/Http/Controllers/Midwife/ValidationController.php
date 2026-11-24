<?php

namespace App\Http\Controllers\Midwife;

use App\Events\AncValidationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAncValidationRequest;
use App\Models\AncRecord;
use App\Models\AncValidation;
use App\Models\DetectedCondition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ValidationController extends Controller
{
    public function show(AncRecord $ancRecord)
    {
        $user = Auth::user();

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
        $user = Auth::user();

        DB::transaction(function () use ($ancRecord, $user, $request): void {

            // Pessimistic lock — cegah dua request bersamaan
            $freshRecord = AncRecord::lockForUpdate()->findOrFail($ancRecord->id);

            // Double-check setelah lock
            if ($freshRecord->is_locked) {
                abort(422, 'Data sudah dikunci.');
            }

            // Proses konfirmasi / penolakan kondisi
            if ($request->filled('conditions')) {
                foreach ($request->conditions as $condData) {

                    $condition = DetectedCondition::findOrFail($condData['id']);

                    // Pastikan kondisi milik ANC record ini
                    if ($condition->anc_record_id !== $freshRecord->id) {
                        abort(403, 'Kondisi tidak valid untuk record ini.');
                    }

                    if ($condData['action'] === 'confirm') {
                        $condition->confirm($user, $condData['notes'] ?? null);
                    } else {
                        $condition->reject($user, $condData['notes'] ?? null);
                    }
                }
            }

            // Kunci record ANC
            $validation = AncValidation::lockedRecord($freshRecord, $user, $request->notes);

            // Dispatch sinkron di dalam batas transaksi agar validasi & aktivasi TTD atomik (SEC-010, ADR-0004)
            AncValidationCreated::dispatch($validation);
        });

        return redirect()
            ->route('midwife.patients.show', $ancRecord->mother_id)
            ->with(
                'success',
                'Data ANC berhasil divalidasi dan dikunci.'
            );
    }
}
