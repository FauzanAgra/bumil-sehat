<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\AncRecord;
use App\Models\AncSchedule;
use App\Models\DetectedCondition;
use App\Models\MotherProfile;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hour = now('Asia/Jakarta')->hour;

        $greeting = match (true) {
            $hour >= 5 && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };

        // ── Wilayah bidan ─────────────────────────────────────────────
        $midwifeAreas = $user->midwifeAreas()
            ->active()
            ->with('area.subdistrict')
            ->get();

        $areaLabel = $midwifeAreas->map(
            fn ($ma) => 'Kel. '.$ma->area->subdistrict->name.' · RW '.$ma->area->rw_number
        )->join(', ') ?: 'Belum ada wilayah';

        // ── Ibu dampingan — Fix A1: eager load semua relasi sekaligus ─
        // Tidak ada query tambahan saat sortByDesc / map di bawah
        $mothers = MotherProfile::where('assigned_midwife_id', $user->id)
            ->with([
                'user',
                'activePregnancy.ancSchedules',
                'ancRecords.detectedConditions',
                'ancRecords.validation',
            ])
            ->get();

        $totalMothers = $mothers->count();

        // ── ANC hari ini ──────────────────────────────────────────────
        $todaySchedules = AncSchedule::whereHas('pregnancy', function ($q) use ($user) {
            $q->whereHas('mother', fn ($q2) => $q2->where('assigned_midwife_id', $user->id));
        })
            ->where('schedule_date', today())
            ->where('status', 'scheduled')
            ->with(['pregnancy.mother.user', 'ancRecord.detectedConditions'])
            ->get();

        // ── Menunggu validasi (scalar count, 1 query) ─────────────────
        $pendingValidation = AncRecord::whereHas(
            'mother',
            fn ($q) => $q->where('assigned_midwife_id', $user->id)
        )->unlocked()->doesntHave('validation')->count();

        // ── Kondisi risiko (1 query) ──────────────────────────────────
        $riskCount = DetectedCondition::where('status', 'detected')
            ->whereHas('ancRecord.mother', fn ($q) => $q->where('assigned_midwife_id', $user->id))
            ->distinct('anc_record_id')
            ->count();

        // ── ANC pending untuk tampil di dashboard (3 terbaru) ─────────
        $pendingRecords = AncRecord::whereHas(
            'mother',
            fn ($q) => $q->where('assigned_midwife_id', $user->id)
        )->unlocked()->doesntHave('validation')
            ->with(['mother.user', 'schedule'])
            ->latest('submitted_at')
            ->take(3)
            ->get();

        // ── Ibu preview — Fix A1: sortByDesc dari collection, 0 query ─
        $motherPreview = $mothers->sortByDesc(function ($m) {
            if (! $m->activePregnancy) {
                return 0;
            }

            // Gunakan relasi yang sudah di-eager load — tidak ada DB query baru
            $hasRisk = $m->ancRecords->contains(
                fn ($r) => $r->detectedConditions->where('status', 'detected')->isNotEmpty()
            );
            $hasPending = $m->ancRecords->contains(
                fn ($r) => ! $r->is_locked && ! $r->validation
            );

            if ($hasRisk) {
                return 3;
            }
            if ($hasPending) {
                return 2;
            }

            return 1;
        })->take(3)->map(function ($m) {
            // Tambahkan status_flag supaya blade bisa pakai langsung
            $hasRisk = $m->ancRecords->contains(
                fn ($r) => $r->detectedConditions->where('status', 'detected')->isNotEmpty()
            );
            $hasPending = $m->ancRecords->contains(
                fn ($r) => ! $r->is_locked && ! $r->validation
            );

            $m->status_flag = $hasRisk ? 'risiko' : ($hasPending ? 'validasi' : 'normal');

            return $m;
        });

        // ── Ringkasan kondisi (1 query dengan GROUP BY) ───────────────
        $conditionSummary = DetectedCondition::where('status', 'detected')
            ->whereHas('ancRecord.mother', fn ($q) => $q->where('assigned_midwife_id', $user->id))
            ->selectRaw('condition_type, COUNT(*) as count')
            ->groupBy('condition_type')
            ->pluck('count', 'condition_type')
            ->toArray();

        $conditionSummary = array_merge(
            ['kek' => 0, 'preeclampsia' => 0, 'anemia' => 0],
            $conditionSummary
        );

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
