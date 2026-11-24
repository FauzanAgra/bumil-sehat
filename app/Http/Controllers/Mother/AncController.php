<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use App\Models\AncRecord;
use App\Models\AncSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AncController extends Controller
{
    // Riwayat semua jadwal ANC
    public function history()
    {
        $user = Auth::user();
        $motherProfile = $user->motherProfile;
        $pregnancy = $motherProfile?->activePregnancy;

        if (! $pregnancy) {
            return redirect()->route('mother.dashboard');
        }

        $schedules = $pregnancy->ancSchedules()
            ->orderBy('anc_number')
            ->get();

        $nextSchedule = $schedules
            ->where('status', 'scheduled')
            ->where('schedule_date', '>=', today()->toDateString())
            ->sortBy('schedule_date')
            ->first();

        return view('dashboard.mother.anc-history', compact(
            'user',
            'pregnancy',
            'schedules',
            'nextSchedule'
        ));
    }

    // ── Detail satu ANC
    public function detail(AncSchedule $schedule)
    {
        $user = Auth::user();
        $motherProfile = $user->motherProfile;

        // Pastikan jadwal ini milik ibu yang login
        abort_if(
            ! $motherProfile || $schedule->pregnancy?->mother_id !== $motherProfile->id,
            403
        );

        $record = $schedule->ancRecord;

        return view('dashboard.mother.anc-detail', compact(
            'user',
            'schedule',
            'record'
        ));
    }

    //  Form input ANC
    public function form(AncSchedule $schedule)
    {
        $user = Auth::user();
        $motherProfile = $user->motherProfile;

        abort_if(
            ! $motherProfile || $schedule->pregnancy?->mother_id !== $motherProfile->id,
            403
        );

        $existingRecord = $schedule->ancRecord;

        // Kalau sudah ada record dan dikunci, atau jadwal tidak layak diisi, redirect ke detail
        if (
            $existingRecord?->is_locked ||
            ! $this->isScheduleEligible($schedule, $motherProfile->activePregnancy?->id, $existingRecord)
        ) {
            return redirect()->route('mother.anc.detail', $schedule);
        }

        // USG wajib di ANC ke-1 dan ke-5
        $usgRequired = in_array($schedule->anc_number, [1, 5], true);

        return view('dashboard.mother.anc-form', compact(
            'user',
            'schedule',
            'existingRecord',
            'usgRequired'
        ));
    }

    //  Simpan data ANC
    public function store(Request $request, AncSchedule $schedule)
    {
        $user = Auth::user();
        $motherProfile = $user->motherProfile;

        abort_if(
            ! $motherProfile || $schedule->pregnancy?->mother_id !== $motherProfile->id,
            403
        );

        $activePregnancyId = $motherProfile->activePregnancy?->id;
        $precheckRecord = $schedule->ancRecord;

        abort_if(
            $precheckRecord?->is_locked,
            422,
            'Data ANC sudah dikunci oleh Bidan.'
        );

        abort_if(
            ! $this->isScheduleEligible($schedule, $activePregnancyId, $precheckRecord),
            422,
            'Jadwal ANC ini tidak layak untuk diisi atau diubah.'
        );

        // USG wajib di ANC ke-1 dan ke-5 jika belum memiliki bukti USG sebelumnya (SEC-012)
        $requiresUsg = in_array($schedule->anc_number, [1, 5], true)
            && ! $precheckRecord?->usgImage()->exists();

        $validated = $request->validate([
            'weight' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'lila' => ['nullable', 'numeric', 'min:10', 'max:50'],
            'systolic' => ['nullable', 'integer', 'min:60', 'max:250'],
            'diastolic' => ['nullable', 'integer', 'min:40', 'max:150'],
            'hb' => ['nullable', 'numeric', 'min:5', 'max:20'],
            'tt_status' => ['nullable', 'in:done,not_done'],
            'fetal_position' => ['nullable', 'in:head_down,transverse,breech'],
            'fetal_heart_rate' => ['nullable', 'integer', 'min:60', 'max:200'],
            'usg_image' => [$requiresUsg ? 'required' : 'nullable', 'image', 'max:2048'],
        ], [
            'usg_image.required' => 'Foto USG wajib diunggah untuk kunjungan ANC ke-1 dan ke-5.',
        ]);

        $clinicalData = collect($validated)->only([
            'weight',
            'height',
            'lila',
            'systolic',
            'diastolic',
            'hb',
            'tt_status',
            'fetal_position',
            'fetal_heart_rate',
        ])->all();

        $clinicalData['recorded_by'] = $user->id;
        $clinicalData['submitted_at'] = now();

        $storedUsgPath = null;

        try {
            DB::transaction(function () use ($request, $schedule, $motherProfile, $user, $clinicalData, &$storedUsgPath): void {
                // Pessimistic lock pada jadwal & record untuk mencegah race condition dengan validasi Bidan (SEC-004)
                $lockedSchedule = AncSchedule::with('pregnancy')
                    ->lockForUpdate()
                    ->findOrFail($schedule->id);

                abort_if(
                    $lockedSchedule->pregnancy?->mother_id !== $motherProfile->id,
                    403
                );

                $currentActivePregnancyId = $motherProfile->activePregnancy()->value('id');

                $existingRecord = AncRecord::where('schedule_id', $lockedSchedule->id)
                    ->lockForUpdate()
                    ->first();

                abort_if(
                    $existingRecord?->is_locked,
                    422,
                    'Data ANC sudah dikunci oleh Bidan.'
                );

                abort_if(
                    ! $this->isScheduleEligible($lockedSchedule, $currentActivePregnancyId, $existingRecord),
                    422,
                    'Jadwal ANC ini tidak layak untuk diisi atau diubah.'
                );

                // Re-check kebutuhan USG di bawah lock
                if (
                    in_array($lockedSchedule->anc_number, [1, 5], true)
                    && ! $existingRecord?->usgImage()->exists()
                    && ! $request->hasFile('usg_image')
                ) {
                    abort(422, 'Foto USG wajib diunggah untuk kunjungan ANC ke-1 dan ke-5.');
                }

                if ($existingRecord) {
                    $existingRecord->fill($clinicalData);
                    $existingRecord->save();
                    $record = $existingRecord;
                } else {
                    $record = new AncRecord($clinicalData);
                    $record->schedule_id = $lockedSchedule->id;
                    $record->mother_id = $lockedSchedule->pregnancy->mother_id;
                    $record->save();
                }

                // Upload USG jika ada
                if ($request->hasFile('usg_image')) {
                    $storedUsgPath = $request->file('usg_image')->store('anc/usg', 'public');
                    $record->usgImage()->updateOrCreate(
                        ['anc_record_id' => $record->id],
                        ['image_url' => $storedUsgPath, 'uploaded_by' => $user->id]
                    );
                }
            });
        } catch (\Throwable $e) {
            if ($storedUsgPath !== null) {
                Storage::disk('public')->delete($storedUsgPath);
            }

            throw $e;
        }

        return redirect()->route('mother.anc.detail', $schedule)
            ->with('success', 'Data ANC berhasil disimpan. Menunggu validasi Bidan.');
    }

    private function isScheduleEligible(AncSchedule $schedule, ?int $activePregnancyId, ?AncRecord $existingRecord): bool
    {
        if (! $schedule->pregnancy || ! $schedule->pregnancy->isActive()) {
            return false;
        }

        if ($activePregnancyId === null || $schedule->pregnancy_id !== $activePregnancyId) {
            return false;
        }

        if ($existingRecord) {
            return ! $existingRecord->is_locked
                && in_array($schedule->status, ['scheduled', 'done'], true);
        }

        return $schedule->status === 'scheduled';
    }
}
