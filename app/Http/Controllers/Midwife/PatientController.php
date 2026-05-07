<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\AncRecord;
use App\Models\DetectedCondition;
use App\Models\MotherProfile;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = MotherProfile::where('assigned_midwife_id', $user->id)
            ->with(['user', 'activePregnancy', 'subdistrict', 'area']);

        // Search
        if ($request->filled('q')) {
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('name', 'like', '%' . $request->q . '%')
            );
        }

        // Filter status
        $filter = $request->get('filter', 'semua');
        if ($filter === 'risiko') {
            $query->whereHas(
                'ancRecords',
                fn($q) =>
                $q->whereHas(
                    'detectedConditions',
                    fn($q2) =>
                    $q2->where('status', 'detected')
                )
            );
        } elseif ($filter === 'validasi') {
            $query->whereHas(
                'ancRecords',
                fn($q) =>
                $q->unlocked()->doesntHave('validation')
            );
        } elseif ($filter === 'normal') {
            $query->whereDoesntHave(
                'ancRecords',
                fn($q) =>
                $q->whereHas(
                    'detectedConditions',
                    fn($q2) =>
                    $q2->where('status', 'detected')
                )
            )->whereDoesntHave(
                'ancRecords',
                fn($q) =>
                $q->unlocked()->doesntHave('validation')
            );
        }

        $mothers = $query->get()->map(function ($m) {
            $pregnancy = $m->activePregnancy;
            $hasRisk   = $pregnancy ? DetectedCondition::where('status', 'detected')
                ->whereHas('ancRecord', fn($q) => $q->where('mother_id', $m->id))
                ->exists() : false;
            $hasPending = $pregnancy ? AncRecord::where('mother_id', $m->id)
                ->unlocked()->doesntHave('validation')->exists() : false;

            $m->status_flag = $hasRisk ? 'risiko' : ($hasPending ? 'validasi' : 'normal');
            $m->conditions  = $hasRisk ? DetectedCondition::where('status', 'detected')
                ->whereHas('ancRecord', fn($q) => $q->where('mother_id', $m->id))
                ->pluck('condition_type')->toArray() : [];
            return $m;
        });

        // Hitung stat
        $stats = [
            'total'   => $mothers->count(),
            'normal'  => $mothers->where('status_flag', 'normal')->count(),
            'validasi' => $mothers->where('status_flag', 'validasi')->count(),
            'risiko'  => $mothers->where('status_flag', 'risiko')->count(),
        ];

        return view('dashboard.midwife.patients', compact(
            'user',
            'mothers',
            'stats',
            'filter'
        ));
    }

    public function show(MotherProfile $motherProfile)
    {
        $user = auth()->user();

        abort_if($motherProfile->assigned_midwife_id !== $user->id, 403);

        $motherProfile->load([
            'user',
            'subdistrict',
            'area',
            'activePregnancy.ancSchedules.ancRecord.detectedConditions',
            'activePregnancy.ancSchedules.ancRecord.validation',
            'activePregnancy.ttdStatus',
        ]);

        $pregnancy = $motherProfile->activePregnancy;

        return view('dashboard.midwife.patient-detail', compact(
            'user',
            'motherProfile',
            'pregnancy'
        ));
    }
}
