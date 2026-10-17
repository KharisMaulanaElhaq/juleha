@extends('layouts.app')

@section('title', 'Masuk ke Portal Peserta – ' . setting('site_name', 'DPD JULEHA Brebes'))
@section('meta_description', 'Masuk atau buat akun portal peserta DPD JULEHA Kabupaten Brebes untuk pendaftaran kegiatan dan materi pelatihan.')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-6 sm:py-10 px-4 portal-bg">
    <div class="w-full max-w-[440px] space-y-4">
        
        <!-- Kartu Utama -->
        <div class="portal-card p-5 sm:p-7 space-y-5">
            
            <!-- Header Kartu: Logo & Judul -->
            <div class="text-center pt-1">
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center group focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)] rounded-xl p-1" title="Kembali ke Beranda">
                    <img src="{{ asset('assets/logo-juleha-brebes.png') }}" 
                         alt="Logo DPD JULEHA Brebes" 
                         class="h-14 sm:h-16 w-auto object-contain mx-auto transition-transform group-hover:scale-105" />
                    <span class="mt-2 text-[11px] font-semibold tracking-wider uppercase text-slate-500 dark:text-slate-400">
                        DPD JULEHA Kabupaten Brebes
                    </span>
                </a>

                <h1 class="mt-2 text-[26px] sm:text-[28px] font-heading font-bold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Masuk ke Portal Peserta
                </h1>
                <p class="mt-1 text-base font-medium text-slate-700 dark:text-slate-200">
                    Akun dibuat otomatis saat pertama kali masuk.
                </p>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Daftar kegiatan dan akses materi pelatihan DPD JULEHA Kabupaten Brebes.
                </p>
            </div>

            <!-- Pesan Error Ramah & Tombol Aksi di Dalam Kartu -->
            @php
                $authError = session('auth_error');
                $generalError = session('error');
                $isError = !empty($authError) || !empty($generalError);
                $errorMsg = $authError['message'] ?? $generalError;
                $actionType = $authError['action'] ?? 'retry';
            @endphp

            @if($isError)
                <div class="p-3.5 rounded-xl border border-rose-300 dark:border-rose-900/60 bg-rose-50/90 dark:bg-rose-950/40 text-slate-800 dark:text-rose-100 text-xs sm:text-sm space-y-2.5" role="alert">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"></i>
                        <div class="leading-relaxed font-medium">
                            {{ $errorMsg }}
                        </div>
                    </div>
                    <div class="pt-1 flex items-center gap-2">
                        @if($actionType === 'contact_panitia' || str_contains((string)$errorMsg, 'Hubungi panitia') || str_contains((string)$errorMsg, 'panitia'))
                            <a href="{{ setting_wa_link('Halo Panitia DPD JULEHA Brebes, saya mengalami kendala masuk akun: ' . $errorMsg) }}" 
                               target="_blank" rel="noopener noreferrer"
                               class="portal-btn-whatsapp px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs hover:opacity-95">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Hubungi Panitia</span>
                            </a>
                        @else
                            <a href="{{ route('auth.google') }}" 
                               class="portal-btn-primary px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                <span>Coba Lagi</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="p-3.5 rounded-xl border portal-border portal-surface-subtle portal-text text-xs sm:text-sm flex items-start gap-2.5" role="status">
                    <i data-lucide="info" class="w-4 h-4 portal-text-muted shrink-0 mt-0.5"></i>
                    <div class="leading-relaxed">{{ session('info') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="p-3.5 rounded-xl border border-emerald-300 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 text-xs sm:text-sm flex items-start gap-2.5" role="status">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                    <div class="leading-relaxed">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Tombol Utama: Lanjutkan dengan Google -->
            <div class="space-y-3">
                <a id="google-login-btn" 
                   href="{{ route('auth.google') }}"
                   class="w-full h-[52px] sm:h-[54px] px-4 rounded-xl flex items-center justify-center gap-3.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-100 font-semibold text-base shadow-xs hover:shadow transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)] focus-visible:ring-offset-2">
                    <!-- Google SVG Logo Resmi -->
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.27 21.43 7.33 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.98 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.27 2.57 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span>Lanjutkan dengan Google</span>
                </a>

                <!-- Checkbox Ingat Saya di Perangkat Ini -->
                <div class="pt-0.5">
                    <label class="flex items-start gap-2.5 cursor-pointer text-xs select-none">
                        <input type="checkbox" 
                               id="remember-google" 
                               name="remember" 
                               value="1" 
                               checked
                               class="w-4 h-4 mt-0.5 rounded text-[var(--primary)] focus:ring-[var(--primary)] border-slate-300 dark:border-slate-600" />
                        <span class="text-slate-700 dark:text-slate-300 leading-tight">
                            <strong class="font-semibold text-slate-800 dark:text-slate-200">Ingat saya di perangkat ini</strong>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-normal">
                                Jangan dicentang di perangkat umum
                            </span>
                        </span>
                    </label>
                </div>

                <!-- Keterangan Privasi Dua Baris -->
                <div class="pt-2 border-t portal-border text-[13px] leading-relaxed text-slate-600 dark:text-slate-300">
                    Kami hanya menerima nama, email, dan foto profil dari akun Google Anda. Kata sandi tidak pernah kami lihat.
                    <a href="{{ route('kebijakan-privasi') }}" class="font-medium text-[var(--primary)] hover:underline inline-flex items-center gap-0.5">
                        <span>Kebijakan Privasi</span>
                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

            <!-- Accordion: Tidak punya akun Google? (Tertutup Default) -->
            <div class="pt-2 border-t portal-border">
                <details id="no-google-accordion" class="group rounded-xl border portal-border portal-surface-subtle transition-all overflow-hidden">
                    <summary class="flex items-center justify-between px-3.5 py-3 cursor-pointer text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 select-none hover:text-[var(--primary)] list-none">
                        <span class="flex items-center gap-2">
                            <i data-lucide="help-circle" class="w-4 h-4 portal-text-muted"></i>
                            <span>Tidak punya akun Google?</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 portal-text-muted transition-transform group-open:rotate-180"></i>
                    </summary>

                    <div class="px-4 pb-4 pt-1 space-y-3.5 border-t portal-border bg-white dark:bg-slate-900/60">
                        @if($allowEmail)
                            <!-- Segmented Tabs Masuk / Buat Akun -->
                            <div class="flex items-center p-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold" role="tablist">
                                <button type="button" 
                                        id="tab-btn-login"
                                        onclick="switchEmailTab('login')"
                                        role="tab" 
                                        aria-selected="true"
                                        aria-controls="pane-email-login"
                                        class="flex-1 py-1.5 px-3 rounded-md transition text-slate-800 dark:text-white bg-white dark:bg-slate-700 shadow-xs">
                                    Masuk
                                </button>
                                <button type="button" 
                                        id="tab-btn-register"
                                        onclick="switchEmailTab('register')"
                                        role="tab" 
                                        aria-selected="false"
                                        aria-controls="pane-email-register"
                                        class="flex-1 py-1.5 px-3 rounded-md transition text-slate-500 dark:text-slate-400 hover:text-slate-800">
                                    Buat akun
                                </button>
                            </div>

                            <!-- Pane: Masuk Email -->
                            <div id="pane-email-login" role="tabpanel" class="space-y-3">
                                <form action="{{ route('login.post') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="email-login" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Alamat Email
                                        </label>
                                        <input type="email" id="email-login" name="email" value="{{ old('email') }}" required
                                               class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                               placeholder="nama@email.com" />
                                        @error('email')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="password-login" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Kata Sandi
                                            </label>
                                            <button type="button" 
                                                    onclick="toggleForgotPassword()" 
                                                    class="text-[11px] text-[var(--primary)] hover:underline focus:outline-none">
                                                Lupa kata sandi?
                                            </button>
                                        </div>
                                        <input type="password" id="password-login" name="password" required
                                               class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                               placeholder="••••••••" />
                                        @error('password')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div id="forgot-password-box" class="hidden p-2.5 rounded-lg portal-surface-subtle border portal-border text-[11px] portal-text-muted leading-relaxed">
                                        Untuk reset kata sandi email Anda, silakan hubungi panitia melalui
                                        <a href="{{ setting_wa_link('Halo Panitia DPD JULEHA Brebes, saya lupa kata sandi akun email saya.') }}" target="_blank" class="font-semibold text-[var(--primary)] hover:underline">
                                            WhatsApp Panitia &rarr;
                                        </a>
                                    </div>

                                    <button type="submit" 
                                            class="w-full h-10 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition">
                                        Masuk
                                    </button>
                                </form>
                            </div>

                            <!-- Pane: Buat Akun Email -->
                            <div id="pane-email-register" role="tabpanel" class="hidden space-y-3">
                                <form action="{{ route('register.post') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="reg-name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Nama Lengkap
                                        </label>
                                        <input type="text" id="reg-name" name="name" value="{{ old('name') }}" required
                                               class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                               placeholder="Contoh: Budi Santoso" />
                                        @error('name')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="reg-email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Alamat Email
                                        </label>
                                        <input type="email" id="reg-email" name="email" value="{{ old('email') }}" required
                                               class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                               placeholder="nama@email.com" />
                                    </div>
                                    <div>
                                        <label for="reg-password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Kata Sandi
                                        </label>
                                        <input type="password" id="reg-password" name="password" required
                                               class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                               placeholder="Minimal 8 karakter" />
                                    </div>
                                    <div>
                                        <label for="reg-password-confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Konfirmasi Kata Sandi
                                        </label>
                                        <input type="password" id="reg-password-confirmation" name="password_confirmation" required
                                               class="w-full h-10 px-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                               placeholder="Ulangi kata sandi" />
                                    </div>
                                    <button type="submit" 
                                            class="w-full h-10 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition">
                                        Buat Akun
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- Jika Pendaftaran Email Tidak Aktif -->
                            <div class="space-y-3 py-1">
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                    Hubungi panitia untuk dibantu proses pendaftaran dan verifikasi akun portal Anda.
                                </p>
                                <a href="{{ setting_wa_link('Halo Panitia DPD JULEHA Brebes, saya ingin dibantu masuk/mendaftar portal peserta karena tidak memiliki akun Google.') }}" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="w-full min-h-[44px] px-4 rounded-xl portal-btn-whatsapp font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xs hover:opacity-95 transition">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    <span>Hubungi Panitia ({{ setting_wa_display('whatsapp_number') }})</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        </div>

        <!-- Tautan Bawah Kartu -->
        <div class="text-center pt-1">
            <a href="{{ url('/admin') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
                Pengurus DPD? Masuk ke panel admin &rarr;
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hubungkan preferensi remember me dengan tombol Google OAuth
        const rememberCheckbox = document.getElementById('remember-google');
        const googleBtn = document.getElementById('google-login-btn');
        if (rememberCheckbox && googleBtn) {
            const baseGoogleUrl = googleBtn.getAttribute('href').split('?')[0];
            rememberCheckbox.addEventListener('change', function() {
                googleBtn.href = baseGoogleUrl + (this.checked ? '' : '?remember=0');
            });
        }
    });

    function switchEmailTab(tab) {
        const loginBtn = document.getElementById('tab-btn-login');
        const regBtn = document.getElementById('tab-btn-register');
        const loginPane = document.getElementById('pane-email-login');
        const regPane = document.getElementById('pane-email-register');

        if (tab === 'login') {
            loginBtn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-800', 'dark:text-white', 'shadow-xs');
            loginBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
            regBtn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-800', 'dark:text-white', 'shadow-xs');
            regBtn.classList.add('text-slate-500', 'dark:text-slate-400');
            loginPane.classList.remove('hidden');
            regPane.classList.add('hidden');
        } else {
            regBtn.classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-800', 'dark:text-white', 'shadow-xs');
            regBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
            loginBtn.classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-800', 'dark:text-white', 'shadow-xs');
            loginBtn.classList.add('text-slate-500', 'dark:text-slate-400');
            regPane.classList.remove('hidden');
            loginPane.classList.add('hidden');
        }
    }

    function toggleForgotPassword() {
        const box = document.getElementById('forgot-password-box');
        if (box) {
            box.classList.toggle('hidden');
        }
    }
</script>
@endsection
