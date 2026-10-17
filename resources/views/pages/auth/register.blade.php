@extends('layouts.app')

@section('title', 'Daftar Akun Peserta – ' . setting('site_name', 'DPD JULEHA Brebes'))
@section('meta_description', 'Pendaftaran akun peserta baru DPD JULEHA Kabupaten Brebes melalui akun Google resmi untuk mengikuti bimtek dan sertifikasi halal.')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-100 to-slate-200 dark:from-slate-950 dark:to-slate-900">
    <div class="max-w-md w-full space-y-6">
        <!-- Header -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-block group mb-3">
                <img src="{{ asset('assets/logo-juleha-brebes.png') }}" alt="DPD JULEHA Brebes" class="h-16 mx-auto object-contain group-hover:scale-105 transition-transform" />
            </a>
            <h1 class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Pendaftaran Akun Peserta
            </h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Buat akun untuk mendaftar bimbingan teknis, sertifikasi, mengunduh materi, dan mencatat presensi.
            </p>
        </div>

        <!-- Alert Messages -->
        @if(session('error'))
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 px-4 py-3 rounded-xl text-sm flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 text-rose-600 dark:text-rose-400 mt-0.5"></i>
                <div class="leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white dark:bg-slate-900 shadow-xl rounded-2xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800">
            <!-- Google Sign Up Button -->
            <div class="space-y-4">
                <a href="{{ route('auth.google') }}"
                   class="w-full flex items-center justify-center gap-3.5 px-4 py-3.5 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-100 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 font-medium text-base shadow-sm hover:shadow transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-600">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.27 21.43 7.33 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.98 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.27 2.57 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span class="font-medium text-slate-800 dark:text-slate-100">Daftar dengan Google</span>
                </a>

                <div class="bg-purple-50/80 dark:bg-purple-950/30 p-3.5 rounded-xl border border-purple-200 dark:border-purple-800/50 text-xs text-purple-900 dark:text-purple-300 flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-[var(--primary)] flex-shrink-0 mt-0.5"></i>
                    <div class="space-y-1">
                        <p class="font-semibold">Masuk ke Portal Peserta</p>
                        <p class="text-purple-800/90 dark:text-purple-300/90 leading-relaxed">
                            Akun dibuat otomatis saat pertama kali masuk. Daftar kegiatan dan akses materi pelatihan DPD JULEHA Kabupaten Brebes.
                        </p>
                    </div>
                </div>
            </div>

            @if($allowEmail)
                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white dark:bg-slate-900 px-3 text-slate-500 dark:text-slate-400 font-medium">Jalur Cadangan: Email & Password</span>
                    </div>
                </div>

                <!-- Form Pendaftaran Cadangan -->
                <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm transition-all"
                               placeholder="Nama lengkap Anda" />
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Email Aktif
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm transition-all"
                               placeholder="nama@email.com" />
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Kata Sandi (Minimal 8 Karakter)
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm transition-all"
                               placeholder="••••••••" />
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Konfirmasi Kata Sandi
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-600 focus:border-transparent text-sm transition-all"
                               placeholder="••••••••" />
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-purple-700 to-indigo-800 hover:from-purple-800 hover:to-indigo-900 shadow-md shadow-purple-900/20 text-sm transition-all">
                        Daftar Akun Peserta
                    </button>
                </form>
            @endif

            <div class="mt-6 pt-6 border-t border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
                Sudah punya akun peserta?
                <a href="{{ route('login') }}" class="font-semibold text-purple-700 dark:text-purple-400 hover:underline">
                    Masuk di sini
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400 px-4">
            Data Anda dilindungi sesuai
            <a href="{{ route('kebijakan-privasi') }}" class="text-purple-700 dark:text-purple-400 hover:underline">
                Kebijakan Privasi
            </a>
            dan standar UU Perlindungan Data Pribadi (PDP).
        </p>
    </div>
</div>
@endsection
