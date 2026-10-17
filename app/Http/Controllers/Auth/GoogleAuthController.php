<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan peserta ke Google OAuth dialog.
     * Scope minimal: openid, email, profile.
     */
    public function redirect(Request $request): RedirectResponse
    {
        // Simpan preferensi remember me peserta (default aktif/true)
        $remember = $request->has('remember') ? $request->boolean('remember') : true;
        $request->session()->put('google_auth_remember', $remember);

        $driver = Socialite::driver('google')->scopes(['openid', 'email', 'profile']);

        // Di lingkungan lokal non-test, samakan host redirect URI dengan host yang sedang dibuka di browser
        // (127.0.0.1 vs localhost) agar sesi/cookie tidak terputus karena beda origin.
        if (app()->environment('local') && ! app()->runningUnitTests()) {
            $configured = config('services.google.redirect');
            if ($configured) {
                $configuredHost = parse_url($configured, PHP_URL_HOST);
                $requestHost = $request->getHost();
                if ($configuredHost && in_array($configuredHost, ['127.0.0.1', 'localhost'], true) && $configuredHost !== $requestHost) {
                    $driver->redirectUrl(str_replace($configuredHost, $requestHost, $configured));
                }
            }
        }

        return $driver->redirect();
    }

    /**
     * Menangani callback dari Google OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        // 1. Tangani pembatalan izin oleh pengguna
        if ($request->has('error')) {
            $error = $request->get('error');
            if ($error === 'access_denied') {
                $msg = 'Anda belum memberi izin. Tekan Lanjutkan dengan Google dan pilih Izinkan.';

                return redirect()->route('login')
                    ->with('error', $msg)
                    ->with('auth_error', [
                        'type' => 'access_denied',
                        'message' => $msg,
                        'action' => 'retry',
                    ]);
            }

            $msg = 'Proses masuk terputus. Silakan coba lagi.';

            return redirect()->route('login')
                ->with('error', $msg)
                ->with('auth_error', [
                    'type' => 'interrupted',
                    'message' => $msg,
                    'action' => 'retry',
                ]);
        }

        $driver = Socialite::driver('google');

        // Samakan host redirect jika di lingkungan lokal
        if (app()->environment('local') && ! app()->runningUnitTests()) {
            $configured = config('services.google.redirect');
            if ($configured) {
                $configuredHost = parse_url($configured, PHP_URL_HOST);
                $requestHost = $request->getHost();
                if ($configuredHost && in_array($configuredHost, ['127.0.0.1', 'localhost'], true) && $configuredHost !== $requestHost) {
                    $driver->redirectUrl(str_replace($configuredHost, $requestHost, $configured));
                }
            }
        }

        try {
            $googleUser = $driver->user();
        } catch (InvalidStateException $e) {
            // Jika terjadi InvalidStateException di lingkungan lokal (mis. perbedaan origin 127.0.0.1 vs localhost),
            // coba selesaikan dengan stateless fallback agar autentikasi peserta tetap berhasil.
            if (app()->environment('local')) {
                try {
                    $statelessDriver = Socialite::driver('google')->stateless();
                    if (! app()->runningUnitTests() && isset($configuredHost, $requestHost) && $configuredHost !== $requestHost) {
                        $statelessDriver->redirectUrl(str_replace($configuredHost, $requestHost, $configured));
                    }
                    $googleUser = $statelessDriver->user();
                } catch (Exception $statelessException) {
                    $msg = 'Proses masuk terputus. Silakan coba lagi.';

                    return redirect()->route('login')
                        ->with('error', $msg)
                        ->with('auth_error', [
                            'type' => 'state_mismatch',
                            'message' => $msg,
                            'action' => 'retry',
                        ]);
                }
            } else {
                $msg = 'Proses masuk terputus. Silakan coba lagi.';

                return redirect()->route('login')
                    ->with('error', $msg)
                    ->with('auth_error', [
                        'type' => 'state_mismatch',
                        'message' => $msg,
                        'action' => 'retry',
                    ]);
            }
        } catch (Exception $e) {
            $msg = 'Gagal berkomunikasi dengan layanan Google. Silakan coba lagi atau hubungi panitia.';

            return redirect()->route('login')
                ->with('error', $msg)
                ->with('auth_error', [
                    'type' => 'google_service',
                    'message' => $msg,
                    'action' => 'contact_panitia',
                ]);
        }

        // 2. Pastikan email terverifikasi oleh Google
        $rawPayload = $googleUser->user ?? [];
        $emailVerified = $rawPayload['email_verified'] ?? true;

        if (! $emailVerified) {
            $msg = 'Akun Google Anda belum memiliki email yang terverifikasi. Hubungi panitia untuk dibantu.';

            return redirect()->route('login')
                ->with('error', $msg)
                ->with('auth_error', [
                    'type' => 'unverified_email',
                    'message' => $msg,
                    'action' => 'contact_panitia',
                ]);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();

        // 3. Cari user berdasarkan google_id
        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            // Jika belum ada, cari berdasarkan email terverifikasi lalu tautkan
            $user = User::where('email', $email)->first();

            if ($user) {
                // Tautkan akun Google ke akun yang sudah ada (jangan ubah role yang sudah ada demi mencegah eskalasi)
                $user->google_id = $googleId;
                if (empty($user->avatar)) {
                    $user->avatar = $googleUser->getAvatar();
                }
                if (empty($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }
                $user->save();
            } else {
                // Buat akun baru dengan role WAJIB participant
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'Peserta JULEHA',
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $googleUser->getAvatar(),
                    'role' => User::ROLE_PARTICIPANT,
                    'email_verified_at' => now(),
                    'password' => null,
                ]);
            }
        } else {
            // Perbarui avatar jika ada yang baru
            if (empty($user->avatar) && $googleUser->getAvatar()) {
                $user->avatar = $googleUser->getAvatar();
                $user->save();
            }
        }

        // 4. Cek apakah akun dinonaktifkan
        if (! $user->is_active) {
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi panitia.');
        }

        // 5. Login dan regenerate sesi (gunakan preferensi remember me peserta, default true)
        $remember = $request->session()->pull('google_auth_remember', true);
        $request->session()->put('login_method', 'google');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        // 6. Jika peserta belum melengkapi profil (NIK belum ada), arahkan ke form lengkapi profil
        // Nilai url.intended tetap dipertahankan di session hingga formulir profil selesai disimpan
        if ($user->role === User::ROLE_PARTICIPANT && (! $user->profile || empty($user->profile->nik))) {
            return redirect()->route('participant.profile')->with('info', 'Silakan lengkapi data profil dan NIK Anda terlebih dahulu.');
        }

        return redirect()->intended(route('dashboard'));
    }
}
