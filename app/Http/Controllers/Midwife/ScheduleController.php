<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\AncSchedule;
use App\Models\MotherProfile;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $filter = $request->get('filter', 'today');

        $query = AncSchedule::whereHas('pregnancy', function ($q) use ($user) {
            $q->whereHas(
                'mother',
                fn($q2) =>
                $q2->where('assigned_midwife_id', $user->id)
            );
        })->with(['pregnancy.mother.user', 'ancRecord.detectedConditions']);

        $query = match ($filter) {
            'today'    => $query->where('schedule_date', today()),
            'tomorrow' => $query->where('schedule_date', today()->addDay()),
            'week'     => $query->whereBetween('schedule_date', [today(), today()->endOfWeek()]),
            default    => $query->where('schedule_date', '>=', today()),
        };

        $schedules = $query->orderBy('schedule_date')->orderBy('schedule_time')->get();

        // Summary
        $summary = [
            'validasi'     => $schedules->filter(
                fn($s) =>
                $s->ancRecord && !$s->ancRecord->is_locked && !$s->ancRecord->validation
            )->count(),
            'belum_input'  => $schedules->filter(fn($s) => !$s->ancRecord)->count(),
            'risiko'       => $schedules->filter(
                fn($s) =>
                $s->ancRecord?->detectedConditions->where('status', 'detected')->isNotEmpty()
            )->count(),
        ];

        return view('dashboard.midwife.schedules', compact(
            'user',
            'schedules',
            'summary',
            'filter'
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $mothers = MotherProfile::where('assigned_midwife_id', $user->id)
            ->with([
                'user',
                'activePregnancy.ancSchedules',
                'ancRecords.detectedConditions',  // ← tambahkan
            ])
            ->get()
            ->filter(fn($m) => $m->activePregnancy)
            ->map(function ($m) {
                $m->has_risk = $m->ancRecords->contains(
                    fn($r) => $r->detectedConditions->where('status', 'detected')->isNotEmpty()
                );
                return $m;
            })
            ->values();

        $selectedMother = null;
        if ($request->filled('mother_id')) {
            $selectedMother = $mothers->firstWhere('id', $request->mother_id);
        }

        return view('dashboard.midwife.add-schedule', compact(
            'user',
            'mothers',
            'selectedMother'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'mother_profile_id' => ['required', 'integer', 'exists:mother_profiles,id'],
            'anc_number' => ['required', 'integer', 'min:1', 'max:6'],
            'schedule_date' => ['required', 'date', 'after_or_equal:today'],
            'schedule_time' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $mother = MotherProfile::where('id', $request->mother_profile_id)
            ->where('assigned_midwife_id', $user->id)  // ← cek sekaligus
            ->firstOrFail();

        $pregnancy = $mother->activePregnancy;

        abort_if(
            !$pregnancy,
            404,
            'Tidak ada kehamilan aktif.'
        );

        abort_if(
            $mother->assigned_midwife_id !== $user->id,
            403
        );

        // Cek duplicate ANC
        $exists = $pregnancy->ancSchedules()
            ->where('anc_number', $request->anc_number)
            ->exists();

        if ($exists) {

            return back()
                ->withInput()
                ->withErrors([
                    'anc_number' =>
                    'Jadwal ANC ke-' .
                        $request->anc_number .
                        ' sudah ada.'
                ]);
        }

        // Create ANC
        $pregnancy->ancSchedules()->create([
            'anc_number' => $request->anc_number,
            'schedule_date' => $request->schedule_date,
            'schedule_time' => $request->schedule_time,
            'note' => $request->note,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('midwife.schedules')
            ->with(
                'success',
                'Jadwal ANC ke-' .
                    $request->anc_number .
                    ' berhasil dibuat.'
            );
    }
}
