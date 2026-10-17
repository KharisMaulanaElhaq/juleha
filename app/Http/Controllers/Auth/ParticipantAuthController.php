<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ParticipantAuthController extends Controller
{
    /**
     * Tampilkan halaman login peserta.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $allowEmail = Setting::get('allow_email_registration', '0') === '1';

        return view('pages.auth.login', [
            'allowEmail' => $allowEmail,
        ]);
    }

    /**
     * Tampilkan halaman pendaftaran peserta (dialihkan ke halaman masuk & daftar terpadu).
     */
    public function showRegisterForm(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login', $request->query());
    }

    /**
     * Proses login dengan email dan kata sandi biasa.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->has('remember') ? $request->boolean('remember') : true;

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('email'))->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Hubungi panitia.',
                ]);
            }

            $request->session()->regenerate();

            if ($user->isParticipant()) {
                return redirect()->intended(route('dashboard'));
            }

            // Jika admin login via portal publik, arahkan ke /admin
            return redirect()->to('/admin')->with('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Email atau kata sandi tidak cocok dengan data kami.',
        ]);
    }

    /**
     * Proses pendaftaran cadangan dengan email & password (jika toggle aktif).
     */
    public function register(Request $request): RedirectResponse
    {
        $allowEmail = Setting::get('allow_email_registration', '0') === '1';

        if (! $allowEmail) {
            return redirect()->route('register')->with('error', 'Pendaftaran akun mandiri melalui email dinonaktifkan oleh administrator. Silakan masuk menggunakan akun Google Anda.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar. Silakan login atau gunakan Masuk dengan Google.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PARTICIPANT,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('participant.profile')->with('info', 'Pendaftaran berhasil! Silakan lengkapi profil Anda (termasuk NIK untuk sertifikat).');
    }

    /**
     * Keluar dari portal peserta.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari akun.');
    }
}
