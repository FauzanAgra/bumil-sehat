<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses berdasarkan role pengguna.
 *
 * Cara pakai di route:
 *   ->middleware('role:midwife')
 *   ->middleware('role:doctor')
 *   ->middleware('role:mother')
 *   ->middleware('role:admin')
 *   ->middleware('role:midwife,doctor')   ← multiple roles (OR)
 *
 * Jika user belum login → redirect ke login (ditangani middleware 'auth' sebelumnya).
 * Jika role tidak cocok → redirect ke dashboard yang sesuai dengan rolenya.
 * Jika belum verified → redirect ke waiting-approval (kecuali mother).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        // Cek role cocok
        if (!in_array($user->role, $roles)) {
            return redirect($this->dashboardFor($user->role));
        }

        // Cek verifikasi untuk tenaga medis (midwife & doctor)
        // Mother tidak perlu verified untuk akses dashboard
        if (in_array($user->role, ['midwife', 'doctor']) && !$user->is_verified) {
            return redirect()->route('waiting-approval');
        }

        return $next($request);
    }

    /**
     * Redirect URL sesuai role user yang sedang login.
     * Dipakai saat user mencoba akses halaman role lain.
     */
    private function dashboardFor(string $role): string
    {
        return match ($role) {
            'mother'  => route('mother.dashboard'),
            'midwife' => route('midwife.dashboard'),
            'doctor'  => route('doctor.dashboard'),
            'admin'   => '/admin', // atau url('/admin')
            default   => route('login'),
        };
    }
}
