<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use App\Models\AncRecord;
use App\Models\AncSchedule;
use Illuminate\Http\Request;

class AncController extends Controller
{
    //Riwayat semua jadwal ANC
    public function history()
    {
        $user          = auth()->user();
        $motherProfile = $user->motherProfile;
        $pregnancy     = $motherProfile?->activePregnancy;

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
        $user          = auth()->user();
        $motherProfile = $user->motherProfile;

        // Pastikan jadwal ini milik ibu yang login
        abort_if(
            $schedule->pregnancy?->mother_id !== $motherProfile->id,
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
        $user          = auth()->user();
        $motherProfile = $user->motherProfile;

        abort_if(
            $schedule->pregnancy->mother_id !== $motherProfile->id,
            403
        );

        // Kalau sudah ada record dan dikunci, redirect ke detail
        if ($schedule->ancRecord?->is_locked) {
            return redirect()->route('mother.anc.detail', $schedule);
        }

        $existingRecord = $schedule->ancRecord;

        // USG wajib di ANC ke-1 dan ke-5
        $usgRequired = in_array($schedule->anc_number, [1, 5]);

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
        $user          = auth()->user();
        $motherProfile = $user->motherProfile;

        abort_if(
            $schedule->pregnancy->mother_id !== $motherProfile->id,
            403
        );

        // Kalau sudah dikunci, tolak
        if ($schedule->ancRecord?->is_locked) {
            return back()->withErrors(['locked' => 'Data ANC sudah dikunci oleh Bidan.']);
        }

        $request->validate([
            'weight'           => ['nullable', 'numeric', 'min:20', 'max:200'],
            'height'           => ['nullable', 'numeric', 'min:100', 'max:250'],
            'lila'             => ['nullable', 'numeric', 'min:10', 'max:50'],
            'systolic'         => ['nullable', 'integer', 'min:60', 'max:250'],
            'diastolic'        => ['nullable', 'integer', 'min:40', 'max:150'],
            'hb'               => ['nullable', 'numeric', 'min:5', 'max:20'],
            'tt_status'        => ['nullable', 'in:done,not_done'],
            'fetal_position'   => ['nullable', 'in:head_down,transverse,breech'],
            'fetal_heart_rate' => ['nullable', 'integer', 'min:60', 'max:200'],
            'usg_image'        => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only([
            'weight',
            'height',
            'lila',
            'systolic',
            'diastolic',
            'hb',
            'tt_status',
            'fetal_position',
            'fetal_heart_rate',
        ]);

        $data['schedule_id']  = $schedule->id;
        $data['mother_id']    = $motherProfile->id;
        $data['recorded_by']  = $user->id;
        $data['submitted_at'] = now();

        // Update atau buat baru
        $record = $schedule->ancRecord ? tap($schedule->ancRecord)->update($data) : AncRecord::create($data);

        // Upload USG jika ada
        if ($request->hasFile('usg_image')) {
            $path = $request->file('usg_image')->store('anc/usg', 'public');
            $record->usgImage()->updateOrCreate(
                ['anc_record_id' => $record->id],
                ['image_url' => $path, 'uploaded_by' => $user->id]
            );
        }

        return redirect()->route('mother.anc.detail', $schedule)
            ->with('success', 'Data ANC berhasil disimpan. Menunggu validasi Bidan.');
    }
}
