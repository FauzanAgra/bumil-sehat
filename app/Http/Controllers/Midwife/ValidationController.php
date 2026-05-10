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

    /**
     * Fix A2: Seluruh operasi validasi dibungkus DB::transaction + lockForUpdate
     * untuk mencegah race condition saat double-submit / dua tab terbuka bersamaan.
     *
     * Fix A2 juga: validasi ownership kondisi (conditions[*][id] harus milik ancRecord ini)
     * untuk mencegah IDOR — bidan tidak bisa submit kondisi milik pasien lain.
     */
    public function validate(StoreAncValidationRequest $request, AncRecord $ancRecord)
    {
        $user = auth()->user();

        DB::transaction(function () use ($ancRecord, $user, $request) {

            // Pessimistic lock — cegah dua request masuk bersamaan
            $freshRecord = AncRecord::lockForUpdate()->findOrFail($ancRecord->id);

            // Double-check setelah lock (race condition guard)
            abort_if($freshRecord->is_locked, 422, 'Data sudah dikunci.');

            // Proses konfirmasi / penolakan kondisi
            if ($request->filled('conditions')) {
                foreach ($request->conditions as $condData) {
                    $condition = DetectedCondition::findOrFail($condData['id']);

                    // IDOR guard: pastikan kondisi ini memang milik ANC record ini
                    abort_if(
                        $condition->anc_record_id !== $freshRecord->id,
                        403,
                        'Kondisi tidak valid untuk record ini.'
                    );

                    if ($condData['action'] === 'confirm') {
                        $condition->confirm($user, $condData['notes'] ?? null);
                    } else {
                        $condition->reject($user, $condData['notes'] ?? null);
                    }
                }
            }

            // Kunci record
            AncValidation::lockedRecord($freshRecord, $user, $request->notes);

            // Auto-aktifkan TTD jika usia kehamilan >= 10 minggu
            $pregnancy = $freshRecord->mother->activePregnancy;

            if ($pregnancy && $pregnancy->gestational_week >= 10) {
                $ttd = $pregnancy->ttdStatus;

                if (! $ttd) {
                    $pregnancy->ttdStatus()->create([
                        'is_active'    => true,
                        'started_at'   => now(),
                        'activated_by' => 'system',
                    ]);
                } elseif (! $ttd->is_active) {
                    $ttd->activate('system');
                }
            }
        });

        return redirect()
            ->route('midwife.patients.show', $ancRecord->mother_id)
            ->with('success', 'Data ANC berhasil divalidasi dan dikunci.');
    }
}
