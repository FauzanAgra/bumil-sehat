<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Update middleware 'guest' bawaan Laravel:
 * Jika user sudah login dan mencoba akses halaman guest (login/register),
 * redirect ke dashboard sesuai rolenya — bukan ke HOME yang hardcoded.
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                $dashboard = match ($user->role) {
                    'mother' => route('mother.dashboard'),
                    'midwife' => route('midwife.dashboard'),
                    'doctor' => route('doctor.dashboard'),
                    'admin' => url('/admin'),
                    default => route('mother.dashboard'),
                };

                return redirect($dashboard);
            }
        }

        return $next($request);
    }
}
