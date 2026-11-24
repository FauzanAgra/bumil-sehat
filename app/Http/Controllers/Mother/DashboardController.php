<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use App\Models\DetectedCondition;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $motherProfile = $user->motherProfile;
        $pregnancy = $motherProfile?->activePregnancy;

        // Data kehamilan
        $gestationalWeek = null;
        $daysUntilNextWeek = null;
        $hpl = null;
        $trimester = null;

        if ($pregnancy) {
            $hpht = Carbon::parse($pregnancy->hpht_date);
            $today = Carbon::today();
            $diffDays = $hpht->diffInDays($today);
            $gestationalWeek = (int) floor($diffDays / 7);
            $daysUntilNextWeek = 7 - ($diffDays % 7);
            $hpl = Carbon::parse($pregnancy->hpl_date);
            $trimester = $pregnancy->trimester;
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
        $ttdStatus = $pregnancy?->ttdStatus;
        $ttdLogs = null;
        $takenToday = false;

        if ($ttdStatus?->is_active) {
            // Ambil 5 hari terakhir + hari ini + 2 hari ke depan untuk tampilan grid
            $ttdLogs = $ttdStatus->logs()
                ->whereBetween('date', [
                    now()->subDays(2)->toDateString(),
                    now()->addDays(2)->toDateString(),
                ])
                ->orderBy('date')
                ->get()
                ->keyBy('date');
            $takenToday = $ttdStatus->isTakenToday();
        }

        // Kondisi risiko yang terdeteksi dan belum divalidasi pada kehamilan aktif
        $pendingConditions = null;
        if ($pregnancy && $motherProfile) {
            $pendingConditions = DetectedCondition::whereHas('ancRecord.schedule', function ($q) use ($pregnancy) {
                $q->where('pregnancy_id', $pregnancy->id);
            })->where('status', 'detected')->exists();
        }

        // Rekomendasi Dokter terbaru (maks 3) pada kehamilan aktif
        $recommendations = $pregnancy
            ? $pregnancy->recommendations()
                ->with(['doctor.medicalProfile.specialization', 'ancRecord.schedule'])
                ->limit(3)
                ->get()
            : collect();

        // Riwayat kehamilan selesai (jika ada)
        $completedPregnancies = $motherProfile
            ? $motherProfile->pregnancies()
                ->completed()
                ->with(['completedBy', 'ancSchedules'])
                ->latest('completed_at')
                ->limit(3)
                ->get()
            : collect();

        // Sapaan berdasarkan waktu
        $hour = now('Asia/Jakarta')->hour;
        $greeting = match (true) {
            $hour >= 5 && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
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
            'recommendations',
            'completedPregnancies',
            'greeting',
        ));
    }

    public function storePregnancy(Request $request): RedirectResponse
    {
        $motherProfile = $request->user()->motherProfile;
        abort_if(! $motherProfile, 403);

        $validated = $request->validate([
            'hpht_date' => ['required', 'date', 'before:today'],
        ], [
            'hpht_date.required' => 'Tanggal HPHT wajib diisi.',
            'hpht_date.date' => 'Format tanggal HPHT tidak valid.',
            'hpht_date.before' => 'Tanggal HPHT harus sebelum hari ini.',
        ]);

        DB::transaction(function () use ($motherProfile, $validated): void {
            $lockedMother = MotherProfile::lockForUpdate()->findOrFail($motherProfile->id);

            abort_if(
                $lockedMother->pregnancies()->active()->exists(),
                422,
                'Bunda masih memiliki kehamilan aktif.'
            );

            $hpht = Carbon::parse($validated['hpht_date']);

            Pregnancy::create([
                'mother_id' => $lockedMother->id,
                'hpht_date' => $hpht->toDateString(),
                'hpl_date' => $hpht->copy()->addDays(280)->toDateString(),
                'status' => 'active',
            ]);
        });

        return redirect()
            ->route('mother.dashboard')
            ->with('success', 'Data kehamilan baru Bunda berhasil dimulai.');
    }
}
