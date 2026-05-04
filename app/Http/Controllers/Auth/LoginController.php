<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        // Kalau sudah login, redirect ke dashboard sesuai role
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'phone.required'    => 'Nomor HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $credentials = [
            'phone'    => $request->phone,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('phone'))
                ->withErrors(['phone' => 'Nomor HP atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole($user): RedirectResponse
    {
        // Kalau belum diverifikasi (bidan/dokter)
        if (! $user->is_verified && in_array($user->role, ['midwife', 'doctor'])) {
            return redirect()->route('waiting-approval');
        }

        return match ($user->role) {
            'admin'   => redirect('/admin'),
            'midwife' => redirect()->route('midwife.dashboard'),
            'doctor'  => redirect()->route('doctor.dashboard'),
            'mother'  => redirect()->route('mother.dashboard'),
            default   => redirect()->route('login'),
        };
    }
}
