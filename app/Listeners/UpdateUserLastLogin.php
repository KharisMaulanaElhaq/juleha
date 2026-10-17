<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

class UpdateUserLastLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        // Jika akun dinonaktifkan atau dihapus, putuskan sesi secara aman
        if (! $user->is_active || $user->trashed()) {
            Auth::logout();
            if (request()->hasSession()) {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }

            return;
        }

        // Tentukan metode masuk (google atau password)
        $method = 'password';
        if (request()->is('auth/google*') || session('login_method') === 'google' || (! empty($user->google_id) && empty($user->password))) {
            $method = 'google';
        }

        // JANGAN menyimpan IP atau perangkat untuk fitur ini
        $user->updateQuietly([
            'last_login_at' => now(),
            'last_login_method' => $method,
        ]);
    }
}
