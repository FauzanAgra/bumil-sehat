<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PregnancyController extends Controller
{
    public function complete(Request $request, Pregnancy $pregnancy): RedirectResponse
    {
        $motherProfile = $pregnancy->mother;

        abort_if(
            ! $motherProfile || $motherProfile->assigned_midwife_id !== $request->user()->id,
            403
        );

        $validated = $request->validate([
            'completion_note' => ['nullable', 'string', 'max:500'],
        ], [
            'completion_note.max' => 'Catatan penutupan maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($pregnancy, $request, $validated): void {
            $lockedPregnancy = Pregnancy::lockForUpdate()->findOrFail($pregnancy->id);

            abort_if(
                ! $lockedPregnancy->isActive(),
                422,
                'Kehamilan ini sudah ditandai selesai.'
            );

            $lockedPregnancy->complete(
                $request->user(),
                $validated['completion_note'] ?? null
            );
        });

        return redirect()
            ->route('midwife.patients.show', $motherProfile->id)
            ->with('success', 'Siklus kehamilan berhasil ditandai selesai dan diarsipkan.');
    }

    public function store(Request $request, MotherProfile $motherProfile): RedirectResponse
    {
        abort_if(
            $motherProfile->assigned_midwife_id !== $request->user()->id,
            403
        );

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
                'Ibu masih memiliki kehamilan aktif. Tutup kehamilan sebelumnya terlebih dahulu.'
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
            ->route('midwife.patients.show', $motherProfile->id)
            ->with('success', 'Kehamilan baru berhasil dimulai.');
    }
}
