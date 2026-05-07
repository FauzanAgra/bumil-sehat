<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\AncRecord;
use App\Models\DetectedCondition;
use App\Models\MotherProfile;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $hour    = now('Asia/Jakarta')->hour;
        $greeting = match (true) {
            $hour >= 5  && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default                   => 'Selamat Malam',
        };

        // Wilayah bidan
        $midwifeAreas = $user->midwifeAreas()
            ->active()
            ->with('area.subdistrict')
            ->get();

        $areaLabel = $midwifeAreas->map(
            fn($ma) =>
            'Kel. ' . $ma->area->subdistrict->name . ' · RW ' . $ma->area->rw_number
        )->join(', ') ?: 'Belum ada wilayah';


        // Ibu dampingan
        $mothers = MotherProfile::where('assigned_midwife_id', $user->id)
            ->with(['activePregnancy.ancSchedules', 'user'])
            ->get();

        $totalMothers = $mothers->count();

        // ANC hari ini
        $todaySchedules = \App\Models\AncSchedule::whereHas('pregnancy', function ($q) use ($user) {
            $q->whereHas('mother', fn($q2) => $q2->where('assigned_midwife_id', $user->id));
        })
            ->where('schedule_date', today())
            ->where('status', 'scheduled')
            ->with(['pregnancy.mother.user', 'ancRecord'])
            ->get();

        // Menunggu validasi
        $pendingValidation = AncRecord::whereHas(
            'mother',
            fn($q) =>
            $q->where('assigned_midwife_id', $user->id)
        )->unlocked()->doesntHave('validation')->count();

        // Kondisi risiko belum dikonfirmasi
        // $riskCount = DetectedCondition::where('status', 'detected')
        //     ->whereHas(
        //         'ancRecord.mother',
        //         fn($q) =>
        //         $q->where('assigned_midwife_id', $user->id)
        //     )->distinct('ancRecord.mother_id')->count();

        $riskCount = DetectedCondition::where('status', 'detected')
            ->whereHas(
                'ancRecord.mother',
                fn($q) =>
                $q->where('assigned_midwife_id', $user->id)
            )
            ->get()
            ->pluck('ancRecord.mother_id')
            ->unique()
            ->count();

        // ANC pending validation — untuk tampil di dashboard
        $pendingRecords = AncRecord::whereHas(
            'mother',
            fn($q) =>
            $q->where('assigned_midwife_id', $user->id)
        )->unlocked()->doesntHave('validation')
            ->with(['mother.user', 'schedule'])
            ->latest('submitted_at')
            ->take(3)
            ->get();

        // Ibu preview — 3 dengan prioritas (risiko dulu, validasi, normal)
        $motherPreview = $mothers->sortByDesc(function ($m) {
            $pregnancy = $m->activePregnancy;
            if (!$pregnancy) return 0;
            $hasRisk      = DetectedCondition::where('status', 'detected')
                ->whereHas('ancRecord', fn($q) => $q->where('mother_id', $m->id))
                ->exists();
            $hasPending   = AncRecord::where('mother_id', $m->id)
                ->unlocked()->doesntHave('validation')->exists();
            if ($hasRisk)    return 3;
            if ($hasPending) return 2;
            return 1;
        })->take(3);

        // Ringkasan kondisi
        $conditionSummary = [
            'kek' => DetectedCondition::where('condition_type', 'kek')
                ->where('status', 'detected')
                ->whereHas('ancRecord.mother', fn($q) => $q->where('assigned_midwife_id', $user->id))
                ->count(),
            'preeclampsia' => DetectedCondition::where('condition_type', 'preeclampsia')
                ->where('status', 'detected')
                ->whereHas('ancRecord.mother', fn($q) => $q->where('assigned_midwife_id', $user->id))
                ->count(),
            'anemia' => DetectedCondition::where('condition_type', 'anemia')
                ->where('status', 'detected')
                ->whereHas('ancRecord.mother', fn($q) => $q->where('assigned_midwife_id', $user->id))
                ->count(),
        ];

        return view('dashboard.midwife.index', compact(
            'user',
            'greeting',
            'areaLabel',
            'totalMothers',
            'todaySchedules',
            'pendingValidation',
            'riskCount',
            'pendingRecords',
            'motherPreview',
            'conditionSummary'
        ));
    }
}
