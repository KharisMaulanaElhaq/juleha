<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureParticipant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_active === false) {
                Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi panitia.');
            }

            // Jika pengguna berperan admin (super_admin, admin_kegiatan, editor_konten),
            // arahkan ke /admin dengan pesan singkat ramah
            if ($user->isStaff()) {
                return redirect()->to('/admin')->with('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');
            }
        }

        return $next($request);
    }
}
