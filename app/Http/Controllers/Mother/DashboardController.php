<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user          = auth()->user();
        $motherProfile = $user->motherProfile;
        $pregnancy     = $motherProfile?->activePregnancy;

        // Data kehamilan
        $gestationalWeek = null;
        $daysUntilNextWeek = null;
        $hpl             = null;
        $trimester       = null;

        if ($pregnancy) {
            $hpht              = Carbon::parse($pregnancy->hpht_date);
            $today             = Carbon::today();
            $diffDays          = $hpht->diffInDays($today);
            $gestationalWeek   = (int) floor($diffDays / 7);
            $daysUntilNextWeek = 7 - ($diffDays % 7);
            $hpl               = Carbon::parse($pregnancy->hpl_date);
            $trimester         = $pregnancy->trimester;
        }

        // Jadwal ANC terdekat
        $nextSchedule = $pregnancy?->ancSchedules()
            ->where('status', 'scheduled')
            ->where('schedule_date', '>=', today())
            ->orderBy('schedule_date')
            ->first();

        // Bidan pendamping
        $midwife = $motherProfile?->assignedMidwife;

        // TTD Status
        $ttdStatus  = $pregnancy?->ttdStatus;
        $ttdLogs    = null;
        $takenToday = false;

        if ($ttdStatus?->is_active) {
            // Ambil 5 hari terakhir + hari ini + 2 hari ke depan untuk tampilan grid
            $ttdLogs    = $ttdStatus->logs()
                ->whereBetween('date', [
                    now()->subDays(2)->toDateString(),
                    now()->addDays(2)->toDateString(),
                ])
                ->orderBy('date')
                ->get()
                ->keyBy('date');
            $takenToday = $ttdStatus->isTakenToday();
        }

        // Kondisi risiko yang terdeteksi dan belum divalidasi
        $pendingConditions = null;
        if ($pregnancy && $motherProfile) {
            $pendingConditions = \App\Models\DetectedCondition::whereHas('ancRecord', function ($q) use ($motherProfile) {
                $q->where('mother_id', $motherProfile->id);
            })->where('status', 'detected')->exists();
        }

        // Sapaan berdasarkan waktu
        $hour     = now('Asia/Jakarta')->hour;
        $greeting = match (true) {
            $hour >= 5  && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default                   => 'Selamat Malam',
        };

        return view('dashboard.mother.index', compact(
            'user',
            'motherProfile',
            'pregnancy',
            'gestationalWeek',
            'daysUntilNextWeek',
            'hpl',
            'trimester',
            'nextSchedule',
            'midwife',
            'ttdStatus',
            'ttdLogs',
            'takenToday',
            'pendingConditions',
            'greeting',
        ));
    }
}
