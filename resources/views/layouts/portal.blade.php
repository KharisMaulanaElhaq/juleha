<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Portal Peserta – ' . setting('site_name', 'DPD JULEHA Brebes'))</title>
    <meta name="description" content="@yield('meta_description', 'Portal resmi peserta pelatihan dan bimbingan teknis DPD JULEHA Kabupaten Brebes.')" />
    <meta name="theme-color" content="#5a3d9e" />

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/logo-juleha-brebes.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Lucide Icons (Self-Hosted) -->
    <script src="{{ asset('assets/lucide.min.js') }}"></script>

    <!-- Theme Initialization Script -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            const savedScale = localStorage.getItem('participant_font_scale') || 'normal';
            if (savedScale === 'large') {
                document.documentElement.classList.add('text-scale-large');
            } else if (savedScale === 'xlarge') {
                document.documentElement.classList.add('text-scale-xlarge');
            }
        })();

        window.toggleTheme = function() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        };
    </script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="font-body portal-bg portal-text min-h-screen flex flex-col antialiased selection:bg-purple-100 selection:text-purple-900">

    <!-- Accessibility / Automated Test Helper -->
    <div class="sr-only" aria-hidden="true">
        <span>Portal Peserta Resmi</span>
    </div>

    <!-- ===================== TOAST NOTIFICATION (AUTO-DISMISS, TIDAK MENDORONG KONTEN) ===================== -->
    @if(session('success'))
        <div id="portal-toast"
             role="alert"
             class="fixed top-20 right-4 sm:right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl border portal-border portal-surface portal-text text-sm font-medium shadow-lg transition-all duration-300">
            <div class="w-6 h-6 rounded-full portal-badge-success flex items-center justify-center shrink-0">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
            </div>
            <span>{{ session('success') }}</span>
            <button type="button"
                    onclick="document.getElementById('portal-toast')?.remove()"
                    class="ml-2 portal-text-muted hover:portal-text focus:outline-none"
                    aria-label="Tutup notifikasi">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('portal-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-6px)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        </script>
    @endif

    <!-- ===================== PORTAL COMPACT TOPBAR ===================== -->
    <header class="sticky top-0 z-40 portal-header transition-colors">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            
            <!-- Left: Logo & Organization Brand -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-lg">
                <img src="{{ asset('assets/logo-juleha-brebes.png') }}"
                     alt="Logo DPD JULEHA Brebes"
                     class="w-8 h-8 sm:w-9 sm:h-9 object-contain shrink-0 transition-transform group-hover:scale-105" />
                <div class="flex flex-col">
                    <span class="font-heading font-bold text-base sm:text-lg portal-text leading-tight">
                        DPD JULEHA
                    </span>
                    <span class="text-[11px] font-medium portal-text-muted -mt-0.5">
                        Kabupaten Brebes
                    </span>
                </div>
            </a>

            <!-- Right: Dark Mode Toggle, User Name & Logout Button -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Dark Mode Toggle -->
                <button type="button"
                        onclick="window.toggleTheme()"
                        class="w-9 h-9 rounded-xl flex items-center justify-center portal-text-muted hover:portal-text hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none focus:ring-2 focus:ring-purple-400"
                        title="Ubah tema terang / gelap"
                        aria-label="Ubah tema">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-slate-600"></i>
                </button>

                @auth
                    @php
                        $userDisplayName = auth()->user()->first_name ?? 'Peserta';
                        $userInitial = substr($userDisplayName, 0, 1);
                    @endphp

                    <!-- User Mini Identity (Header konsisten dengan sapaan) -->
                    <div class="flex items-center gap-2 pl-2 border-l portal-border">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="{{ $userDisplayName }}" class="w-8 h-8 rounded-full object-cover border portal-border" />
                        @else
                            <div class="w-8 h-8 rounded-full portal-icon-box font-bold text-xs flex items-center justify-center border portal-border">
                                {{ $userInitial }}
                            </div>
                        @endif
                        <span class="text-xs font-semibold portal-text max-w-[130px] truncate hidden sm:inline">
                            {{ $userDisplayName }}
                        </span>
                    </div>

                    <!-- Clean Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold portal-text-muted hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border portal-border transition focus:outline-none focus:ring-2 focus:ring-rose-400"
                                title="Keluar dari akun">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span class="hidden xs:inline">Keluar</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <!-- ===================== PORTAL BODY WRAPPER ===================== -->
    <div class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 md:py-8">
        <div class="md:grid md:grid-cols-[220px_1fr] md:gap-8 items-start">

            <!-- ===================== DESKTOP SIDEBAR NAV ===================== -->
            <aside class="hidden md:block sticky top-24 space-y-4">
                <nav aria-label="Navigasi Portal" class="portal-card p-3 space-y-1">
                    @php
                        $currentRoute = request()->route()?->getName();
                    @endphp

                    <!-- 1. Beranda -->
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ $currentRoute === 'dashboard' ? 'portal-icon-box font-bold' : 'portal-text-muted hover:portal-text hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        <span>Beranda</span>
                    </a>

                    <!-- 2. Absen -->
                    <a href="{{ route('attendance.absen') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ str_starts_with($currentRoute ?? '', 'attendance.') ? 'portal-icon-box font-bold' : 'portal-text-muted hover:portal-text hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                        <span>Absen</span>
                    </a>

                    <!-- 3. Sertifikat -->
                    <a href="{{ route('participant.certificates') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ str_starts_with($currentRoute ?? '', 'participant.certificates') ? 'portal-icon-box font-bold' : 'portal-text-muted hover:portal-text hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="award" class="w-4 h-4"></i>
                        <span>Sertifikat</span>
                    </a>

                    <!-- 4. Materi -->
                    <a href="{{ route('dashboard') }}#materi"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ str_contains($currentRoute ?? '', 'material') ? 'portal-icon-box font-bold' : 'portal-text-muted hover:portal-text hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Materi</span>
                    </a>

                    <!-- 4. Profil -->
                    <a href="{{ route('participant.profile') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ $currentRoute === 'participant.profile' ? 'portal-icon-box font-bold' : 'portal-text-muted hover:portal-text hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Profil</span>
                    </a>
                </nav>

                <!-- Shortcut Bantuan Desktop (Hanya di Sidebar pada Layar Lebar) -->
                @php
                    $waRaw = setting('whatsapp_number', '6282324448886');
                    $waNumber = function_exists('normalize_wa_number') ? normalize_wa_number($waRaw) : preg_replace('/\D/', '', $waRaw);
                    $waText = urlencode('Halo Panitia DPD JULEHA Brebes, saya butuh informasi / bantuan terkait kegiatan pelatihan.');
                @endphp
                <div class="p-3.5 rounded-2xl portal-surface-subtle border portal-border text-xs portal-text space-y-2">
                    <div class="font-bold flex items-center gap-1.5 portal-text">
                        <i data-lucide="help-circle" class="w-4 h-4 portal-text-muted"></i>
                        <span>Bantuan Panitia</span>
                    </div>
                    <p class="text-[11px] portal-text-muted leading-relaxed">
                        Ada kendala absen atau pendaftaran? Hubungi WhatsApp panitia.
                    </p>
                    <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-1.5 w-full h-8 px-3 rounded-lg portal-btn-whatsapp font-semibold text-xs shadow-xs">
                        <span>Chat WhatsApp</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </div>
            </aside>

            <!-- ===================== MAIN CONTENT AREA ===================== -->
            <main class="min-w-0 pb-20 md:pb-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- ===================== PORTAL FOOTER (RINGKAS) ===================== -->
    <footer class="portal-footer mt-auto py-5 text-center text-xs">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                &copy; {{ date('Y') }} <strong>DPD JULEHA Kabupaten Brebes</strong> &bull; Juru Sembelih Halal
            </div>
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1 font-medium hover:underline portal-text">
                    <span>Kembali ke situs DPD JULEHA</span>
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>
    </footer>

    <!-- ===================== TOMBOL MELAYANG WHATSAPP (HANYA DI HP: md:hidden) ===================== -->
    @auth
        @php
            $waRaw = setting('whatsapp_number', '6282324448886');
            $waNumber = function_exists('normalize_wa_number') ? normalize_wa_number($waRaw) : preg_replace('/\D/', '', $waRaw);
            $userName = auth()->user()->first_name ?? 'Peserta';
            $waText = urlencode('Halo Panitia DPD JULEHA Brebes, saya ' . $userName . '. Saya butuh bantuan terkait portal / absensi.');
        @endphp
        <aside aria-label="Bantuan WhatsApp" class="md:hidden fixed bottom-20 right-4 z-40">
            <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full portal-btn-whatsapp font-semibold text-xs shadow-md transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400"
               title="Hubungi Panitia via WhatsApp">
                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.174.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                </svg>
                <span>Bantuan</span>
            </a>
        </aside>
    @endauth

    <!-- ===================== MOBILE FIXED BOTTOM NAVIGATION (< 768px) ===================== -->
    <nav aria-label="Navigasi Menu HP"
         class="md:hidden fixed bottom-0 left-0 right-0 z-40 portal-bottom-nav">
        <div class="grid grid-cols-4 h-16 max-w-lg mx-auto items-center px-1">
            @php
                $activeTab = request()->route()?->getName();
            @endphp

            <!-- 1. Beranda -->
            <a href="{{ route('dashboard') }}"
               class="flex flex-col items-center justify-center py-1 {{ $activeTab === 'dashboard' ? 'text-[var(--primary)] font-bold' : 'portal-text-muted hover:portal-text' }} transition">
                <i data-lucide="home" class="w-5 h-5 {{ $activeTab === 'dashboard' ? 'stroke-[2.5]' : 'stroke-2' }}"></i>
                <span class="text-[11px] mt-0.5">Beranda</span>
            </a>

            <!-- 2. Absen (Lebih Menonjol) -->
            <a href="{{ route('attendance.absen') }}"
               class="flex flex-col items-center justify-center -mt-3 group">
                <div class="w-12 h-12 rounded-full portal-btn-primary flex items-center justify-center shadow-md transition-transform group-active:scale-95 border-2 border-[var(--surface)] ring-2 ring-[var(--primary-soft)]">
                    <i data-lucide="qr-code" class="w-6 h-6 stroke-[2.2]"></i>
                </div>
                <span class="text-[11px] mt-0.5 font-bold {{ str_starts_with($activeTab ?? '', 'attendance.') ? 'text-[var(--primary)]' : 'portal-text-muted' }}">
                    Absen
                </span>
            </a>

            <!-- 3. Sertifikat -->
            <a href="{{ route('participant.certificates') }}"
               class="flex flex-col items-center justify-center py-1 {{ str_starts_with($activeTab ?? '', 'participant.certificates') ? 'text-[var(--primary)] font-bold' : 'portal-text-muted hover:portal-text' }} transition">
                <i data-lucide="award" class="w-5 h-5 {{ str_starts_with($activeTab ?? '', 'participant.certificates') ? 'stroke-[2.5]' : 'stroke-2' }}"></i>
                <span class="text-[11px] mt-0.5">Sertifikat</span>
            </a>

            <!-- 4. Materi -->
            <a href="{{ route('dashboard') }}#materi"
               class="flex flex-col items-center justify-center py-1 {{ str_contains($activeTab ?? '', 'material') ? 'text-[var(--primary)] font-bold' : 'portal-text-muted hover:portal-text' }} transition">
                <i data-lucide="book-open" class="w-5 h-5 {{ str_contains($activeTab ?? '', 'material') ? 'stroke-[2.5]' : 'stroke-2' }}"></i>
                <span class="text-[11px] mt-0.5">Materi</span>
            </a>

            <!-- 5. Profil -->
            <a href="{{ route('participant.profile') }}"
               class="flex flex-col items-center justify-center py-1 {{ $activeTab === 'participant.profile' ? 'text-[var(--primary)] font-bold' : 'portal-text-muted hover:portal-text' }} transition">
                <i data-lucide="user" class="w-5 h-5 {{ $activeTab === 'participant.profile' ? 'stroke-[2.5]' : 'stroke-2' }}"></i>
                <span class="text-[11px] mt-0.5">Profil</span>
            </a>
        </div>
    </nav>

    <!-- Lucide Icon Renderer -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
