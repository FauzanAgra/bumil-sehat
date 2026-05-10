<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MotherProfile;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $filter = $request->get('filter', 'semua');
        $search = $request->get('search');

        // FIX: Semua kondisi search dibungkus dalam where() closure
        // supaya tidak merusak scope whereHas('activePregnancy') di luar
        $allMothers = MotherProfile::whereHas('activePregnancy')
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
                    // Search NIK exact match
                    if (preg_match('/^\d{16}$/', $search)) {
                        $inner->orWhere(function ($nikQuery) use ($search) {
                            $nikQuery->byNik($search);
                        });
                    }
                });
            })
            ->get()
            ->map(function ($m) {
                $hasRisk = $m->ancRecords->contains(
                    fn($r) => $r->detectedConditions
                        ->whereIn('status', ['confirmed', 'detected'])
                        ->isNotEmpty()
                );
                $hasPending = $m->ancRecords->contains(
                    fn($r) => !$r->is_locked && !$r->validation
                );
                $m->status_flag = $hasRisk ? 'risiko' : ($hasPending ? 'validasi' : 'normal');
                $m->active_conditions = $m->ancRecords
                    ->flatMap(fn($r) => $r->detectedConditions->whereIn('status', ['confirmed', 'detected']))
                    ->unique('condition_type')
                    ->values();
                return $m;
            });

        // Hitung counts dari semua ibu (sebelum filter status)
        // sehingga angka di tab tidak berubah ketika filter aktif
        $counts = [
            'total'   => $allMothers->count(),
            'risiko'  => $allMothers->filter(fn($m) => $m->status_flag === 'risiko')->count(),
            'validasi' => $allMothers->filter(fn($m) => $m->status_flag === 'validasi')->count(),
            'normal'  => $allMothers->filter(fn($m) => $m->status_flag === 'normal')->count(),
        ];

        // Filter status diterapkan setelah counts dihitung
        $mothers = $filter !== 'semua'
            ? $allMothers->filter(fn($m) => $m->status_flag === $filter)->values()
            : $allMothers;

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
        $user = auth()->user();

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
                'number'   => $n,
                'schedule' => $schedule,
                'record'   => $schedule?->ancRecord,
            ];
        });

        // Kondisi aktif (confirmed / detected) dari semua ANC
        $activeConditions = collect();
        if ($pregnancy) {
            $latestRecord = $pregnancy->ancSchedules->map(fn($s) => $s->ancRecord)->filter(fn($r) => $r && $r->validation)->sortByDesc('created_at')->first();
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
