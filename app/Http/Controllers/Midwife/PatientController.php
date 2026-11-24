<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\TtdStatus;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = MotherProfile::query()
            ->where('assigned_midwife_id', $user->id)
            ->with([
                'user',
                'activePregnancy',
                'subdistrict',
                'area',
                'ancRecords.detectedConditions',
                'ancRecords.validation',
            ]);

        // Search
        if ($request->filled('q')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%');
            });
        }

        // Filter status
        $filter = $request->get('filter', 'semua');

        if ($filter === 'risiko') {
            $query->whereHas('ancRecords', function ($q) {
                $q->whereHas('detectedConditions', function ($q2) {
                    $q2->where('status', 'detected');
                });
            });
        } elseif ($filter === 'validasi') {
            $query->whereHas('ancRecords', function ($q) {
                $q->unlocked()->doesntHave('validation');
            });
        } elseif ($filter === 'normal') {
            $query->whereDoesntHave('ancRecords', function ($q) {
                $q->whereHas('detectedConditions', function ($q2) {
                    $q2->where('status', 'detected');
                });
            })->whereDoesntHave('ancRecords', function ($q) {
                $q->unlocked()->doesntHave('validation');
            });
        }

        // Statistik tetap dihitung dari seluruh pasien bidan
        $allMothers = (clone $query)->get()->map(function ($m) {

            $hasRisk = $m->ancRecords->contains(
                fn ($r) => $r->detectedConditions
                    ->where('status', 'detected')
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

        $stats = [
            'total' => $allMothers->count(),
            'normal' => $allMothers->where('status_flag', 'normal')->count(),
            'validasi' => $allMothers->where('status_flag', 'validasi')->count(),
            'risiko' => $allMothers->where('status_flag', 'risiko')->count(),
        ];

        // Pagination
        $mothers = $query->paginate(20)
            ->through(function ($m) {
                $hasRisk = $m->ancRecords->contains(
                    fn ($r) => $r->detectedConditions
                        ->where('status', 'detected')
                        ->isNotEmpty()
                );

                $hasPending = $m->ancRecords->contains(
                    fn ($r) => ! $r->is_locked && ! $r->validation
                );

                $m->status_flag = $hasRisk
                    ? 'risiko'
                    : ($hasPending ? 'validasi' : 'normal');

                $m->conditions = $hasRisk
                    ? $m->ancRecords
                        ->flatMap(
                            fn ($r) => $r->detectedConditions
                                ->where('status', 'detected')
                        )
                        ->pluck('condition_type')
                        ->unique()
                        ->values()
                        ->toArray()
                    : [];

                return $m;
            });

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

        abort_if(
            $motherProfile->assigned_midwife_id !== $user->id,
            403
        );

        $motherProfile->load([
            'user',
            'subdistrict',
            'area',
            'activePregnancy.ancSchedules.ancRecord.detectedConditions',
            'activePregnancy.ancSchedules.ancRecord.validation',
            'activePregnancy.ttdStatus.logs',
            'activePregnancy.recommendations.doctor.medicalProfile.specialization',
            'activePregnancy.recommendations.ancRecord.schedule',
        ]);

        $pregnancy = $motherProfile->activePregnancy;
        $recommendations = $pregnancy?->recommendations ?? collect();

        $completedPregnancies = $motherProfile->pregnancies()
            ->completed()
            ->with(['completedBy', 'ancSchedules.ancRecord', 'ttdStatus'])
            ->latest('completed_at')
            ->get();

        if ($pregnancy) {
            $pregnancy->setRelation('mother', $motherProfile);
            foreach ($pregnancy->ancSchedules as $ancSchedule) {
                $ancSchedule->setRelation('pregnancy', $pregnancy);
            }
        }

        $activeConditions = collect();

        $ttdStatus = null;
        $ttdTaken = 0;
        $ttdTarget = 90;
        $ttdPercentage = 0;
        $ttdStartedWeek = null;

        if ($pregnancy) {

            foreach ($pregnancy->ancSchedules as $schedule) {

                if ($schedule->ancRecord) {

                    $activeConditions = $activeConditions->merge(
                        $schedule->ancRecord->detectedConditions
                            ->where('status', 'detected')
                    );
                }
            }

            $activeConditions = $activeConditions
                ->unique('condition_type')
                ->values();

            $completedAnc = $pregnancy->ancSchedules
                ->filter(fn ($schedule) => $schedule->ancRecord?->is_locked)
                ->count();

            $progressPercentage = round(($completedAnc / 6) * 100);

            $timeline = collect();

            for ($i = 1; $i <= 6; $i++) {

                $schedule = $pregnancy->ancSchedules
                    ->firstWhere('anc_number', $i);

                $record = $schedule?->ancRecord;

                $hasRisk = $record?->detectedConditions
                    ->where('status', 'detected')
                    ->isNotEmpty() ?? false;

                $hasPending = $record &&
                    ! $record->is_locked &&
                    ! $record->validation;

                $isLocked = $record?->is_locked ?? false;

                $isMissed = $schedule?->status === 'missed';

                $isScheduled = $schedule && ! $record && ! $isMissed;

                $timeline->push([
                    'anc_number' => $i,
                    'schedule' => $schedule,
                    'record' => $record,
                    'has_risk' => $hasRisk,
                    'has_pending' => $hasPending,
                    'is_locked' => $isLocked,
                    'is_scheduled' => $isScheduled,
                    'is_missed' => $isMissed,
                    'need_usg' => in_array($i, [1, 5]),
                ]);
            }

            // TTD
            $ttdStatus = $pregnancy->ttdStatus;

            $ttdTaken = $ttdStatus?->total_taken ?? 0;

            $ttdPercentage = $ttdTarget > 0
                ? min(round(($ttdTaken / $ttdTarget) * 100), 100)
                : 0;

            $ttdStartedWeek = null;

            if ($ttdStatus?->started_at) {

                $hpht = Carbon::parse($pregnancy->hpht_date);

                $startedAt = Carbon::parse($ttdStatus->started_at);

                $diffDays = $hpht->diffInDays($startedAt);

                $ttdStartedWeek = (int) floor($diffDays / 7);
            }
        } else {

            $completedAnc = 0;
            $progressPercentage = 0;
            $timeline = collect();
        }

        $conditionStyles = [
            'preeclampsia' => [
                'bg' => 'bg-red-100',
                'text' => 'text-red-600',
                'icon' => 'fa-heart-pulse',
            ],

            'kek' => [
                'bg' => 'bg-orange-100',
                'text' => 'text-orange-600',
                'icon' => 'fa-weight-scale',
            ],

            'anemia' => [
                'bg' => 'bg-yellow-100',
                'text' => 'text-yellow-600',
                'icon' => 'fa-droplet',
            ],
        ];

        return view('dashboard.midwife.patient-detail', compact(
            'user',
            'motherProfile',
            'pregnancy',
            'activeConditions',
            'completedAnc',
            'progressPercentage',
            'timeline',
            'conditionStyles',
            'ttdStatus',
            'ttdTaken',
            'ttdTarget',
            'ttdPercentage',
            'ttdStartedWeek',
            'recommendations',
            'completedPregnancies',
        ));
    }

    public function toggleTtd(Pregnancy $pregnancy)
    {
        abort_if(
            $pregnancy->mother->assigned_midwife_id !== auth()->id(),
            403
        );

        $ttdStatus = DB::transaction(function () use ($pregnancy): TtdStatus {
            $lockedPregnancy = Pregnancy::lockForUpdate()->findOrFail($pregnancy->id);

            abort_if(
                ! $lockedPregnancy->isActive(),
                422,
                'Kehamilan sudah selesai sehingga status TTD tidak dapat diubah.'
            );

            $ttdStatus = TtdStatus::where('pregnancy_id', $lockedPregnancy->id)
                ->lockForUpdate()
                ->first();

            if (! $ttdStatus) {
                try {
                    return TtdStatus::create([
                        'pregnancy_id' => $lockedPregnancy->id,
                        'is_active' => true,
                        'started_at' => now(),
                        'activated_by' => 'midwife',
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $ttdStatus = TtdStatus::where('pregnancy_id', $lockedPregnancy->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }
            }

            $nextActive = ! $ttdStatus->is_active;

            $ttdStatus->update([
                'is_active' => $nextActive,
                'started_at' => $nextActive ? ($ttdStatus->started_at ?? now()) : $ttdStatus->started_at,
                'activated_by' => 'midwife',
            ]);

            return $ttdStatus->fresh();
        });

        return response()->json([
            'success' => true,
            'is_active' => $ttdStatus->is_active,
        ]);
    }
}
