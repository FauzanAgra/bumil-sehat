<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\AncRecord;
use App\Models\AncValidation;
use App\Models\DetectedCondition;
use Illuminate\Http\Request;

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

    public function validate(Request $request, AncRecord $ancRecord)
    {
        $user = auth()->user();

        abort_if(
            $ancRecord->mother->assigned_midwife_id !== $user->id,
            403
        );

        abort_if($ancRecord->is_locked, 422, 'Data sudah dikunci.');

        $request->validate([
            'notes'                   => ['nullable', 'string', 'max:500'],
            'conditions'              => ['nullable', 'array'],
            'conditions.*.id'         => ['required', 'exists:detected_conditions,id'],
            'conditions.*.action'     => ['required', 'in:confirm,reject'],
            'conditions.*.notes'      => ['nullable', 'string'],
        ]);

        // Proses kondisi
        if ($request->filled('conditions')) {
            foreach ($request->conditions as $condData) {
                $condition = DetectedCondition::findOrFail($condData['id']);
                if ($condData['action'] === 'confirm') {
                    $condition->confirm($user, $condData['notes'] ?? null);
                } else {
                    $condition->reject($user, $condData['notes'] ?? null);
                }
            }
        }

        // Kunci record
        AncValidation::lockedRecord($ancRecord, $user, $request->notes);

        // Aktifkan TTD otomatis jika usia kehamilan >= 10 minggu
        $pregnancy = $ancRecord->mother->activePregnancy;
        if ($pregnancy && $pregnancy->gestational_week >= 10) {
            $ttd = $pregnancy->ttdStatus;
            if (!$ttd) {
                $pregnancy->ttdStatus()->create([
                    'is_active'    => true,
                    'started_at'   => now(),
                    'activated_by' => 'system',
                ]);
            } elseif (!$ttd->is_active) {
                $ttd->activate('system');
            }
        }

        return redirect()->route('midwife.patients.show', $ancRecord->mother_id)
            ->with('success', 'Data ANC berhasil divalidasi dan dikunci.');
    }
}
