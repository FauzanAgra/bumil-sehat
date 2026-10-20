<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MotherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'semua');
        $search = $request->get('search');

        $query = MotherProfile::query()
            ->whereHas('activePregnancy')
            ->with([
                'user',
                'activePregnancy.ancSchedules',
                'ancRecords.detectedConditions',
                'ancRecords.validation',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    // Search nama user
                    $inner->whereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });

                    // Search NIK exact
                    if (preg_match('/^\d{16}$/', $search)) {

                        $inner->orWhere(function ($nikQuery) use ($search) {
                            $nikQuery->byNik($search);
                        });
                    }
                });
            });

        // Counts tetap dari seluruh data
        $allMothers = (clone $query)->get()
            ->map(function ($m) {
                $hasRisk = $m->ancRecords->contains(
                    fn ($r) => $r->detectedConditions
                        ->whereIn('status', ['confirmed', 'detected'])
                        ->isNotEmpty()
                );

                $hasPending = $m->ancRecords->contains(
                    fn ($r) => ! $r->is_locked && ! $r->validation
                );

                $m->status_flag = $hasRisk
                    ? 'risiko'
                    : ($hasPending ? 'validasi' : 'normal');

                return $m;
            });

        $counts = [
            'total' => $allMothers->count(),
            'risiko' => $allMothers->where('status_flag', 'risiko')->count(),
            'validasi' => $allMothers->where('status_flag', 'validasi')->count(),
            'normal' => $allMothers->where('status_flag', 'normal')->count(),
        ];

        // Filter query sebelum pagination
        if ($filter !== 'semua') {

            $query->whereHas('ancRecords', function ($q) use ($filter) {

                if ($filter === 'risiko') {

                    $q->whereHas('detectedConditions', function ($dc) {
                        $dc->whereIn('status', ['confirmed', 'detected']);
                    });
                } elseif ($filter === 'validasi') {

                    $q->where('is_locked', false)
                        ->doesntHave('validation');
                }
            });
        }

        $mothers = $query->paginate(20)
            ->through(function ($m) {

                $hasRisk = $m->ancRecords->contains(
                    fn ($r) => $r->detectedConditions
                        ->whereIn('status', ['confirmed', 'detected'])
                        ->isNotEmpty()
                );

                $hasPending = $m->ancRecords->contains(
                    fn ($r) => ! $r->is_locked && ! $r->validation
                );

                $m->status_flag = $hasRisk
                    ? 'risiko'
                    : ($hasPending ? 'validasi' : 'normal');

                $m->active_conditions = $m->ancRecords
                    ->flatMap(
                        fn ($r) => $r->detectedConditions
                            ->whereIn('status', ['confirmed', 'detected'])
                    )
                    ->unique('condition_type')
                    ->values();

                return $m;
            });

        return view('dashboard.doctor.patients', compact(
            'user',
            'mothers',
            'filter',
            'search',
            'counts'
        ));
    }

    public function show(MotherProfile $motherProfile)
    {
        $user = Auth::user();

        abort_if(
            ! $motherProfile->activePregnancy,
            404,
            'Data tidak ditemukan atau kehamilan sudah tidak aktif.'
        );

        $motherProfile->load([
            'user',
            'subdistrict',
            'area',
            'activePregnancy.ancSchedules.ancRecord.detectedConditions',
            'activePregnancy.ancSchedules.ancRecord.validation.midwife',
            'activePregnancy.ancSchedules.ancRecord.usgImage',
            'assignedMidwife',
        ]);

        $pregnancy = $motherProfile->activePregnancy;

        // Timeline ANC 1–6: isi slot kosong dengan null
        $ancTimeline = collect(range(1, 6))->map(function ($n) use ($pregnancy) {
            $schedule = $pregnancy?->ancSchedules->firstWhere('anc_number', $n);

            return [
                'number' => $n,
                'schedule' => $schedule,
                'record' => $schedule?->ancRecord,
            ];
        });

        // Kondisi aktif (confirmed / detected) dari semua ANC
        $activeConditions = collect();
        if ($pregnancy) {
            $latestRecord = $pregnancy->ancSchedules->map(fn ($s) => $s->ancRecord)->filter(fn ($r) => $r && $r->validation)->sortByDesc('created_at')->first();
            if ($latestRecord) {
                $activeConditions = $latestRecord->detectedConditions->whereIn('status', ['confirmed', 'detected'])->values();
            }
        }

        return view('dashboard.doctor.patient-detail', compact(
            'user',
            'motherProfile',
            'pregnancy',
            'ancTimeline',
            'activeConditions',
        ));
    }
}
