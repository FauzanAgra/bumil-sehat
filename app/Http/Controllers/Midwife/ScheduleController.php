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
        $user = auth()->user();
        $filter = $request->get('filter', 'today');

        $query = AncSchedule::whereHas('pregnancy', function ($q) use ($user) {
            $q->where('status', 'active')
                ->whereHas(
                    'mother',
                    fn ($q2) => $q2->where('assigned_midwife_id', $user->id)
                );
        })->where('status', '!=', 'missed')
            ->with(['pregnancy.mother.user', 'ancRecord.detectedConditions']);

        $query = match ($filter) {
            'today' => $query->where('schedule_date', today()),
            'tomorrow' => $query->where('schedule_date', today()->addDay()),
            'week' => $query->whereBetween('schedule_date', [today(), today()->endOfWeek()]),
            default => $query->where('schedule_date', '>=', today()),
        };

        $schedules = $query->orderBy('schedule_date')->orderBy('schedule_time')->get();

        // Summary dari collection — tidak ada query tambahan
        $summary = [
            'validasi' => $schedules->filter(
                fn ($s) => $s->ancRecord && ! $s->ancRecord->is_locked && ! $s->ancRecord->validation
            )->count(),
            'belum_input' => $schedules->filter(fn ($s) => ! $s->ancRecord)->count(),
            'risiko' => $schedules->filter(
                fn ($s) => $s->ancRecord?->detectedConditions->where('status', 'detected')->isNotEmpty()
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
                'ancRecords.detectedConditions',
            ])
            ->get()
            ->filter(fn ($m) => $m->activePregnancy)
            ->map(function ($m) {
                $m->has_risk = $m->ancRecords->contains(
                    fn ($r) => $r->detectedConditions->where('status', 'detected')->isNotEmpty()
                );

                $schedules = $m->activePregnancy->ancSchedules;

                // ANC yang locked (selesai) — tidak bisa dipilih lagi
                $m->completed_anc = $schedules
                    ->filter(fn ($s) => $s->ancRecord?->is_locked)
                    ->pluck('anc_number')
                    ->toArray();

                // ANC yang terjadwal (belum selesai, bukan missed) — tidak bisa duplikat
                $m->scheduled_anc = $schedules
                    ->filter(fn ($s) => $s->status === 'scheduled')
                    ->pluck('anc_number')
                    ->toArray();

                // ANC yang missed — tampil merah, tidak bisa dipilih
                $m->missed_anc = $schedules
                    ->filter(fn ($s) => $s->status === 'missed')
                    ->pluck('anc_number')
                    ->toArray();

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
            'mother_profile_id' => ['required', 'integer'],
            'anc_number' => ['required', 'integer', 'min:1', 'max:6'],
            'schedule_date' => ['required', 'date', 'after_or_equal:today'],
            'schedule_time' => ['nullable', 'in:08:00,09:00,10:00,11:00,13:00,14:00'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // Fix A5: cek ownership sekaligus — 1 query, langsung 404 jika tidak valid
        $mother = MotherProfile::where('id', $request->mother_profile_id)
            ->where('assigned_midwife_id', $user->id)
            ->firstOrFail();

        $pregnancy = $mother->activePregnancy;

        abort_if(
            ! $pregnancy,
            404,
            'Tidak ada kehamilan aktif untuk ibu ini.'
        );

        // Cek duplikat ANC
        $exists = $pregnancy->ancSchedules()
            ->where('anc_number', $request->anc_number)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'anc_number' => 'Jadwal ANC ke-'.$request->anc_number.' sudah ada.',
                ]);
        }

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
            ->with('success', 'Jadwal ANC ke-'.$request->anc_number.' berhasil dibuat.');
    }

    public function markMissedDirect(Request $request, MotherProfile $motherProfile)
    {
        $user = auth()->user();

        abort_if(
            $motherProfile->assigned_midwife_id !== $user->id,
            403
        );

        $request->validate([
            'anc_number' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $pregnancy = $motherProfile->activePregnancy;

        abort_if(! $pregnancy, 404, 'Tidak ada kehamilan aktif.');

        $exists = $pregnancy->ancSchedules()
            ->where('anc_number', $request->anc_number)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'ANC ke-'.$request->anc_number.' sudah ada.',
            ], 422);
        }

        $pregnancy->ancSchedules()->create([
            'anc_number' => $request->anc_number,
            'schedule_date' => now()->toDateString(),
            'status' => 'missed',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'ANC ke-'.$request->anc_number.' ditandai sebagai terlewat.');
    }

    public function markMissed(AncSchedule $schedule)
    {
        $schedule->load('pregnancy.mother');

        abort_if(
            optional($schedule->pregnancy?->mother)->assigned_midwife_id !== auth()->id(),
            403
        );

        abort_if(
            $schedule->ancRecord()->exists(),
            422,
            'Jadwal yang sudah diisi tidak dapat ditandai missed.'
        );

        abort_if(
            $schedule->status === 'missed',
            422,
            'Jadwal ini sudah ditandai missed.'
        );

        $schedule->update(['status' => 'missed']);

        return back()->with('success', 'ANC ke-'.$schedule->anc_number.' ditandai sebagai terlewat.');
    }

    public function update(Request $request, AncSchedule $schedule)
    {
        $request->validate([
            'schedule_date' => ['required', 'date'],
            'schedule_time' => ['nullable', 'in:08:00,09:00,10:00,11:00,13:00,14:00'],
        ]);

        $schedule->load('pregnancy.mother');

        abort_if(
            optional($schedule->pregnancy?->mother)->assigned_midwife_id !== auth()->id(),
            403
        );

        if ($schedule->ancRecord) {
            return back()->with('error', 'Jadwal yang sudah diisi tidak dapat diubah.');
        }

        $schedule->update([
            'schedule_date' => $request->schedule_date,
            'schedule_time' => $request->schedule_time,
        ]);

        return back()->with('success', 'Jadwal ANC berhasil diperbarui.');
    }
}
