<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\MidwifeArea;
use App\Models\Subdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // ── Setting / Profil ──────────────────────────────────────────────
    public function setting()
    {
        $user = auth()->user();
        $motherProfile = $user->motherProfile;

        return view('dashboard.mother.setting', compact('user', 'motherProfile'));
    }

    // ── Edit Profil ───────────────────────────────────────────────────
    public function edit()
    {
        $user = auth()->user();
        $motherProfile = $user->motherProfile;
        $subdistricts = Subdistrict::orderBy('name')->get();
        $areas = Area::with('subdistrict')->orderBy('rw_number')->get();

        return view('dashboard.mother.edit-profile', compact(
            'user',
            'motherProfile',
            'subdistricts',
            'areas'
        ));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $motherProfile = $user->motherProfile;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone,'.$user->id],
            'email' => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string'],
            'subdistrict_id' => ['required', 'exists:subdistricts,id'],
            'area_id' => [
                'required',
                Rule::exists('areas', 'id')->where('subdistrict_id', $request->integer('subdistrict_id')),
            ],
            'rt' => ['nullable', 'string', 'max:10'],
        ], [
            'phone.unique' => 'Nomor HP sudah digunakan akun lain.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'area_id.exists' => 'RW yang dipilih tidak sesuai dengan Kelurahan.',
        ]);

        DB::transaction(function () use ($request, $user, $motherProfile): void {
            // Update user
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            // Cek apakah wilayah berubah
            $areaChanged = $motherProfile->area_id != $request->integer('area_id');

            // Update mother profile
            $motherProfile->update([
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'subdistrict_id' => $request->integer('subdistrict_id'),
                'area_id' => $request->integer('area_id'),
                'rt' => $request->rt,
            ]);

            // Kalau wilayah berubah, reassign bidan
            if ($areaChanged) {
                $midwifeArea = MidwifeArea::where('area_id', $request->integer('area_id'))
                    ->where('is_active', true)
                    ->first();

                $motherProfile->update([
                    'assigned_midwife_id' => $midwifeArea?->midwife_id,
                ]);
            }
        });

        return redirect()->route('mother.setting')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    // ── Ubah Password ─────────────────────────────────────────────────
    public function changePassword()
    {
        $user = auth()->user();

        return view('dashboard.mother.change-password', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if (! Hash::check($request->old_password, auth()->user()->password)) {
            return back()->withErrors([
                'old_password' => 'Kata sandi lama tidak sesuai.',
            ]);
        }

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
