<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', setting('site_name', 'DPD JULEHA Kabupaten Brebes') . ' – Juru Sembelih Halal')</title>
    <meta name="description" content="@yield('meta_description', 'Situs resmi ' . setting('site_name', 'DPD JULEHA Kabupaten Brebes') . '. Pusat informasi edukasi fiqih sembelih, bimbingan teknis, sertifikasi kompetensi BNSP, dan pengawasan hewan kurban syar’i & higienis.')" />
    <meta name="theme-color" content="#33204f" />

    <!-- Open Graph & Social Cards -->
    <meta property="og:title" content="@yield('title', setting('site_name', 'DPD JULEHA Kabupaten Brebes') . ' – Juru Sembelih Halal')" />
    <meta property="og:description" content="@yield('meta_description', 'Situs resmi ' . setting('site_name', 'DPD JULEHA Kabupaten Brebes') . '. Pusat informasi edukasi, bimtek, dan sertifikasi kompetensi BNSP.')" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ asset('assets/logo-juleha-brebes.png') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('title', setting('site_name', 'DPD JULEHA Kabupaten Brebes') . ' – Juru Sembelih Halal')" />
    <meta name="twitter:description" content="@yield('meta_description', 'Situs resmi ' . setting('site_name', 'DPD JULEHA Kabupaten Brebes') . '.')" />

    <!-- Structured Data Organization (JSON-LD) -->
    @php
        $jsonLdOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => setting('site_name', 'DPD JULEHA Kabupaten Brebes'),
            'url' => url('/'),
            'logo' => asset('assets/logo-juleha-brebes.png'),
        ];
        if (setting('whatsapp_number')) {
            $jsonLdOrg['telephone'] = '+' . normalize_wa_number(setting('whatsapp_number'));
        }
        if (setting('admin_email')) {
            $jsonLdOrg['email'] = setting('admin_email');
        }
        if (setting('office_address')) {
            $jsonLdOrg['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => setting('office_address'),
                'addressLocality' => 'Brebes',
                'addressRegion' => 'Jawa Tengah',
                'addressCountry' => 'ID',
            ];
        }
    @endphp
    <script type="application/ld+json">
    {!! json_encode($jsonLdOrg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/logo-juleha-brebes.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- AOS CSS -->
    <link rel="stylesheet" href="{{ asset('assets/aos.css') }}" />

    <!-- Lucide Icons (Self-Hosted) -->
    <script src="{{ asset('assets/lucide.min.js') }}"></script>

    <!-- Theme & Font Size Initialization Script -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Aksesibilitas Pembesaran Teks Lansia (Normal, Besar, Sangat Besar)
            const savedScale = localStorage.getItem('participant_font_scale') || 'normal';
            if (savedScale === 'large') {
                document.documentElement.classList.add('text-scale-large');
            } else if (savedScale === 'xlarge') {
                document.documentElement.classList.add('text-scale-xlarge');
            }
        })();

        // Fungsi Global untuk Mengubah Skala Tulisan
        window.setFontScale = function(level) {
            document.documentElement.classList.remove('text-scale-large', 'text-scale-xlarge');
            if (level === 'large') {
                document.documentElement.classList.add('text-scale-large');
                localStorage.setItem('participant_font_scale', 'large');
            } else if (level === 'xlarge') {
                document.documentElement.classList.add('text-scale-xlarge');
                localStorage.setItem('participant_font_scale', 'xlarge');
            } else {
                localStorage.setItem('participant_font_scale', 'normal');
            }
            window.dispatchEvent(new CustomEvent('fontscalechanged', { detail: { scale: level } }));
        };

        window.cycleFontScale = function() {
            const current = localStorage.getItem('participant_font_scale') || 'normal';
            let next = 'normal';
            if (current === 'normal') next = 'large';
            else if (current === 'large') next = 'xlarge';
            else next = 'normal';
            window.setFontScale(next);
            return next;
        };
    </script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="font-body bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen">

    <!-- ===================== PRELOADER ===================== -->
    <div id="preloader">
        <div class="flex flex-col items-center gap-4 text-center px-4">
            <!-- JULEHA Logo -->
            <div class="animate-pulse flex items-center justify-center">
                <img src="{{ asset('assets/juleha-darah.png') }}"
                     alt="DPD JULEHA Kabupaten Brebes"
                     class="h-20 sm:h-24 md:h-28 w-auto max-w-[280px] sm:max-w-[340px] object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.6)]" />
            </div>

            <div class="text-white font-heading text-xl font-bold tracking-wide mt-1">DPD JULEHA KABUPATEN BREBES</div>
            <div class="text-brand-highlight text-sm">Juru Sembelih Halal &bull; Syar'i &amp; Higienis</div>
        </div>
    </div>

    <!-- ===================== NAVBAR ===================== -->
    <x-navbar />

    <!-- ===================== MAIN CONTENT ===================== -->
    <main>
        @yield('content')
    </main>

    <!-- ===================== FOOTER ===================== -->
    <x-footer />


    <!-- ===================== SCROLL TO TOP ===================== -->
    <button id="scroll-top" aria-label="Kembali ke atas">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>

    <!-- ===================== TOAST NOTIFICATION ===================== -->
    <div id="toast"
        class="fixed top-20 right-4 bg-brand text-white px-5 py-3 rounded-lg shadow-lg font-medium text-sm translate-x-full opacity-0 transition-all duration-300 z-[200] flex items-center gap-2 border border-white/20">
        <i data-lucide="check-circle" class="w-5 h-5 text-brand-highlight"></i>
        <span id="toast-msg">Pesan berhasil dikirim!</span>
    </div>

    <!-- ===================== FLOATING BANTUAN WHATSAPP (PESERTA) ===================== -->
    @auth
        @if(auth()->user()->isParticipant())
            @php
                $waRaw = setting('whatsapp_number', '6282324448886');
                $waNumber = function_exists('normalize_wa_number') ? normalize_wa_number($waRaw) : preg_replace('/\D/', '', $waRaw);
                $waText = urlencode('Halo Panitia DPD JULEHA Brebes, saya butuh bantuan terkait absensi / kegiatan pelatihan.');
            @endphp
            <aside aria-label="Bantuan Panitia" class="fixed bottom-6 right-4 sm:right-6 z-50">
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2.5 px-4 py-3 sm:px-5 sm:py-3.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-full shadow-2xl border-2 border-white/60 font-bold text-sm sm:text-base transition-transform hover:scale-105 active:scale-95 group focus:outline-none focus:ring-4 focus:ring-emerald-400">
                    <svg class="w-6 h-6 fill-current flex-shrink-0 text-white" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.174.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                    </svg>
                    <span>Butuh bantuan?</span>
                </a>
            </aside>
        @endif
    @endauth

    <!-- ===================== SCRIPTS ===================== -->
    <script src="{{ asset('assets/aos.js') }}"></script>
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
