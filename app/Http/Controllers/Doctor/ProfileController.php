<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load([
            'medicalProfile.specialization',
        ]);

        return view('dashboard.doctor.setting', compact('user'));
    }

    /**
     * Form edit data diri (nama, email).
     * Nomor HP tidak bisa diubah sendiri — harus via Admin.
     */
    public function edit()
    {
        $user = auth()->user()->load([
            'medicalProfile.specialization',
        ]);

        return view('dashboard.doctor.profile-edit', compact('user'));
    }

    /**
     * Simpan perubahan data diri.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email,'.$user->id],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()
            ->route('doctor.profile.setting')
            ->with('success', 'Data diri berhasil diperbarui.');
    }

    /**
     * Form ubah password.
     */
    public function changePassword()
    {
        $user = auth()->user();

        return view('dashboard.doctor.profile-password', compact('user'));
    }

    /**
     * Proses ubah password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('doctor.profile.setting')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
