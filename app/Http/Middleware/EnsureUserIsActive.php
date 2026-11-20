<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Akun yang dinonaktifkan admin langsung keluar pada permintaan berikutnya. */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        }

        return $next($request);
    }
}
