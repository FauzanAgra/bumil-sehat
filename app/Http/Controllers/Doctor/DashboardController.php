<?php

namespace App\Http\Controllers\Doctor;

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

        // ── Semua ibu hamil aktif di Puskesmas (dokter lihat semua, bukan per wilayah)
        // Sesuai PRD: Dokter = supervisi seluruh ibu hamil
        $mothers = MotherProfile::whereHas('activePregnancy')
            ->with([
                'user',
                'activePregnancy',
                'ancRecords.detectedConditions',
                'ancRecords.validation',
            ])
            ->get();

        $totalMothers = $mothers->count();

        // ── Hitung status per ibu (dari collection, tidak N+1)
        $riskCount = $mothers->filter(function ($m) {
            return $m->ancRecords->contains(
                fn ($r) => $r->detectedConditions->where('status', 'confirmed')->isNotEmpty()
            );
        })->count();

        $pendingCount = $mothers->filter(function ($m) {
            return $m->ancRecords->contains(
                fn ($r) => ! $r->is_locked && ! $r->validation
            );
        })->count();

        $normalCount = $totalMothers - $riskCount - $pendingCount;

        // ── ANC minggu ini
        $ancThisWeek = AncSchedule::whereBetween('schedule_date', [today(), today()->endOfWeek()])
            ->where('status', 'scheduled')
            ->count();

        // ── Distribusi kondisi (confirmed = sudah dikonfirmasi bidan, relevan untuk dokter)
        $conditionSummary = DetectedCondition::whereIn('status', ['confirmed', 'detected'])
            ->selectRaw('condition_type, COUNT(*) as count')
            ->groupBy('condition_type')
            ->pluck('count', 'condition_type')
            ->toArray();

        $conditionSummary = array_merge(
            ['kek' => 0, 'preeclampsia' => 0, 'anemia' => 0],
            $conditionSummary
        );

        // ── Ibu dengan kondisi risiko (confirmed/detected) — tampil di dashboard
        $riskMothers = $mothers->filter(function ($m) {
            return $m->ancRecords->contains(
                fn ($r) => $r->detectedConditions
                    ->whereIn('status', ['confirmed', 'detected'])
                    ->isNotEmpty()
            );
        })->take(5)->values();

        // ── ANC terbaru yang sudah dikunci (locked) — untuk preview dashboard
        $recentAnc = AncRecord::where('is_locked', true)
            ->with(['mother.user', 'schedule'])
            ->latest('submitted_at')
            ->take(3)
            ->get();

        return view('dashboard.doctor.index', compact(
            'user',
            'totalMothers',
            'riskCount',
            'pendingCount',
            'normalCount',
            'ancThisWeek',
            'conditionSummary',
            'riskMothers',
            'recentAnc'
        ));
    }
}
