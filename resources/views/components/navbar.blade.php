<!-- ===================== TOP BAR ===================== -->
<div class="topbar py-1.5 sm:py-2 text-white text-xs sm:text-sm border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-2">
            <!-- Left: WA Link (Single clean thin row on mobile) -->
            <div class="flex items-center gap-4 text-xs sm:text-sm min-w-0">
                @if(setting('whatsapp_number'))
                    <a href="{{ setting_wa_link() }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:text-brand-highlight transition-colors truncate">
                        <span class="w-2 h-2 bg-brand-highlight rounded-full animate-pulse flex-shrink-0"></span>
                        <strong class="text-brand-highlight font-medium hidden sm:inline">Sekretariat DPD JULEHA Brebes:</strong>
                        <span class="text-brand-highlight font-medium sm:hidden">WhatsApp:</span>
                        <span class="text-slate-200 truncate">{{ setting_wa_display('whatsapp_number') }}</span>
                    </a>
                @else
                    <span class="flex items-center gap-2 truncate">
                        <span class="w-2 h-2 bg-brand-highlight rounded-full animate-pulse flex-shrink-0"></span>
                        <strong class="text-brand-highlight font-medium">Sekretariat DPD JULEHA Brebes</strong>
                    </span>
                @endif

                @if(setting('admin_email'))
                    <span class="hidden md:flex items-center gap-2 text-slate-300">
                        <span>|</span>
                        <a href="mailto:{{ setting('admin_email') }}" class="hover:text-brand-highlight transition-colors flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-brand-highlight"></i>
                            <span>{{ setting('admin_email') }}</span>
                        </a>
                    </span>
                @endif
            </div>

            <!-- Right: Office Hours (Hidden on mobile <640px, moved to hamburger & footer) -->
            @if(setting('office_hours'))
                <div class="hidden sm:flex items-center gap-1.5 text-brand-highlight/90 text-xs flex-shrink-0">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <span>Layanan: {{ setting('office_hours') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ===================== NAVBAR ===================== -->
<nav id="navbar" class="sticky top-0 z-50 bg-transparent transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 sm:h-16 lg:h-20">
            <!-- Logo + Brand Name (One line, 56-64px height on mobile) -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3 group flex-shrink-0">
                <img src="{{ asset('assets/logo-juleha-brebes.png') }}" alt="Logo DPD JULEHA Brebes" class="h-9 sm:h-11 lg:h-12 w-auto object-contain group-hover:scale-105 transition-transform flex-shrink-0" />
                <div class="min-w-0">
                    <div class="font-heading font-extrabold text-base sm:text-lg lg:text-xl leading-tight text-slate-900 dark:text-white flex items-center gap-1.5 whitespace-nowrap">
                        <span>DPD JULEHA</span>
                        <span class="hidden sm:inline-block text-xs bg-brand-accent text-slate-900 px-2 py-0.5 rounded font-bold">BREBES</span>
                    </div>
                    <div class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 leading-none mt-0.5 whitespace-nowrap">
                        <span class="sm:hidden">Kab. Brebes</span>
                        <span class="hidden sm:inline">Juru Sembelih Halal</span>
                    </div>
                </div>
            </a>

            <!-- Desktop Nav (768px+ / lg:flex) -->
            <div class="hidden lg:flex items-center gap-6 xl:gap-7">
                <a href="{{ route('home') }}" class="nav-link text-sm {{ request()->routeIs('home') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Beranda</a>
                <a href="{{ route('tentang') }}" class="nav-link text-sm {{ request()->routeIs('tentang') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Tentang</a>
                <a href="{{ route('pengurus') }}" class="nav-link text-sm {{ request()->routeIs('pengurus') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Pengurus</a>
                <a href="{{ route('program.index') }}" class="nav-link text-sm {{ request()->routeIs('program.*') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Program</a>
                <a href="{{ route('bimtek.index') }}" class="nav-link text-sm {{ request()->routeIs('bimtek.*') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Bimtek</a>
                <a href="{{ route('berita.index') }}" class="nav-link text-sm {{ request()->routeIs('berita.*') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Berita</a>
                <a href="{{ route('galeri') }}" class="nav-link text-sm {{ request()->routeIs('galeri') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Galeri</a>
                <a href="{{ route('kontak') }}" class="nav-link text-sm {{ request()->routeIs('kontak') ? 'text-brand dark:text-brand-dark font-semibold' : 'text-slate-700 dark:text-slate-200' }}">Kontak</a>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Desktop Actions (Hidden on mobile < 1024px) -->
                <div class="hidden lg:flex items-center gap-2 sm:gap-3">
                    <button type="button" onclick="toggleTheme()" aria-label="Ganti mode tema"
                        class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="dark:hidden">
                            <i data-lucide="moon" class="w-5 h-5"></i>
                        </span>
                        <span class="hidden dark:inline">
                            <i data-lucide="sun" class="w-5 h-5 text-amber-400"></i>
                        </span>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 btn-primary text-sm py-2 px-4 shadow">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard Peserta</span>
                        </a>
                    @else
                        @php
                            $hasOpenBimtek = \App\Models\Event::where('status', 'open')->get()->filter->canRegister()->isNotEmpty();
                        @endphp
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-400 transition-colors">
                            <i data-lucide="log-in" class="w-4 h-4 text-purple-600"></i>
                            <span>Portal Peserta</span>
                        </a>
                        <a href="{{ route('bimtek.index') }}" class="inline-flex btn-primary text-sm py-2.5 px-5">
                            <i data-lucide="{{ $hasOpenBimtek ? 'clipboard-check' : 'calendar' }}" class="w-4 h-4"></i>
                            <span>{{ $hasOpenBimtek ? 'Daftar Bimtek' : 'Jadwal Bimtek' }}</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Header Actions (Visible only on mobile < 1024px) -->
                <div class="flex lg:hidden items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-brand hover:bg-brand/90 shadow-sm transition-colors" title="Buka Dashboard Peserta">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            <span>Portal</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-semibold text-brand dark:text-brand-light bg-brand/10 dark:bg-brand/20 hover:bg-brand/15 border border-brand/20 transition-colors" title="Masuk ke Portal Peserta">
                            <i data-lucide="log-in" class="w-3.5 h-3.5 text-brand dark:text-brand-light"></i>
                            <span>Portal Peserta</span>
                        </a>
                    @endauth

                    <!-- Mobile Menu Toggle Button (Min 44x44px touch target) -->
                    <button id="mobile-menu-btn" aria-label="Menu Navigasi" class="w-10 h-10 sm:w-11 sm:h-11 flex items-center justify-center rounded-xl text-slate-800 dark:text-slate-100 hover:text-brand hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors focus:outline-none focus:ring-2 focus:ring-brand">
                        <i data-lucide="menu" class="w-6 h-6" id="menu-open"></i>
                        <i data-lucide="x" class="w-6 h-6 hidden" id="menu-close"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu (Slide-down with 48px touch items, CTA, and contact details) -->
    <div id="mobile-menu" class="hidden lg:hidden bg-slate-900/98 dark:bg-slate-950/98 backdrop-blur-xl border-t border-white/10 shadow-2xl max-h-[calc(100vh-56px)] overflow-y-auto">
        <div class="max-w-lg mx-auto px-4 py-5 flex flex-col gap-1">
            <!-- Akses Utama Portal Peserta di bagian atas drawer -->
            @auth
                <div class="p-3.5 mb-2 rounded-2xl bg-gradient-to-r from-brand/40 to-purple-900/40 border border-brand/30 text-white flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[11px] text-brand-highlight uppercase font-bold tracking-wider">Akun Peserta</div>
                        <div class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</div>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn-primary text-xs py-2 px-3.5 rounded-xl flex-shrink-0 flex items-center gap-1.5 shadow">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                        <span>Buka Dashboard</span>
                    </a>
                </div>
            @else
                <div class="p-3.5 mb-2 rounded-2xl bg-gradient-to-r from-brand/30 to-slate-800 border border-white/10 text-white flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[11px] text-brand-highlight uppercase font-bold tracking-wider">Portal Peserta</div>
                        <div class="text-xs text-slate-300">Akses bimtek, materi & sertifikat</div>
                    </div>
                    <a href="{{ route('login') }}" class="btn-primary text-xs py-2 px-3.5 rounded-xl flex-shrink-0 flex items-center gap-1.5 shadow">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                        <span>Masuk Portal</span>
                    </a>
                </div>
            @endauth

            <!-- Nav Links (min-height 48px each for touch comfort) -->
            <a href="{{ route('home') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('home') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Beranda</span>
            </a>
            <a href="{{ route('tentang') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('tentang') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Tentang Kami</span>
            </a>
            <a href="{{ route('pengurus') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('pengurus') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Struktur Pengurus</span>
            </a>
            <a href="{{ route('program.index') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('program.*') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Program Kerja</span>
            </a>
            <a href="{{ route('bimtek.index') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('bimtek.*') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Jadwal Bimtek</span>
            </a>
            <a href="{{ route('berita.index') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('berita.*') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Berita &amp; Artikel</span>
            </a>
            <a href="{{ route('galeri') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('galeri') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Galeri Dokumentasi</span>
            </a>
            <a href="{{ route('faq') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('faq') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Tanya Jawab (FAQ)</span>
            </a>
            <a href="{{ route('kontak') }}" class="flex items-center min-h-[48px] px-3.5 rounded-xl text-base font-medium {{ request()->routeIs('kontak') ? 'bg-brand/20 text-brand-highlight font-semibold' : 'text-slate-200 hover:bg-white/5' }}">
                <span>Kontak &amp; Sekretariat</span>
            </a>

            <!-- Action Buttons at bottom of navigation -->
            <div class="pt-3 pb-2 border-t border-white/10 mt-2 space-y-2.5">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary w-full justify-center min-h-[48px] text-sm font-semibold flex items-center gap-2 rounded-xl">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Dashboard Peserta</span>
                    </a>
                @else
                    <a href="{{ route('bimtek.index') }}" class="btn-primary w-full justify-center min-h-[48px] text-sm font-semibold flex items-center gap-2 rounded-xl shadow-lg">
                        <i data-lucide="{{ ($hasOpenBimtek ?? false) ? 'clipboard-check' : 'calendar' }}" class="w-4 h-4"></i>
                        <span>{{ ($hasOpenBimtek ?? false) ? 'Daftar Bimtek Sekarang' : 'Lihat Jadwal Bimtek' }}</span>
                    </a>
                    <a href="{{ route('login') }}" class="w-full justify-center min-h-[44px] text-sm font-medium text-brand-highlight hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 flex items-center gap-2 rounded-xl transition">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk ke Portal Peserta</span>
                    </a>
                @endauth
            </div>

            <!-- Kontak & Layanan Info (Dipindahkan dari Topbar) -->
            <div class="pt-4 border-t border-white/10 mt-2 space-y-2 text-xs text-slate-300">
                <div class="font-semibold text-slate-200 uppercase tracking-wider text-[11px] mb-2">Informasi Sekretariat</div>
                @if(setting('whatsapp_number'))
                    <a href="{{ setting_wa_link() }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 py-1.5 hover:text-brand-highlight transition text-slate-300">
                        <i data-lucide="phone" class="w-4 h-4 text-brand-highlight flex-shrink-0"></i>
                        <span>WhatsApp: <strong class="text-white">{{ setting_wa_display('whatsapp_number') }}</strong></span>
                    </a>
                @endif
                @if(setting('admin_email'))
                    <a href="mailto:{{ setting('admin_email') }}" class="flex items-center gap-2 py-1.5 hover:text-brand-highlight transition text-slate-300">
                        <i data-lucide="mail" class="w-4 h-4 text-brand-highlight flex-shrink-0"></i>
                        <span>Email: <strong class="text-white">{{ setting('admin_email') }}</strong></span>
                    </a>
                @endif
                @if(setting('office_hours'))
                    <div class="flex items-center gap-2 py-1.5 text-slate-300">
                        <i data-lucide="clock" class="w-4 h-4 text-brand-highlight flex-shrink-0"></i>
                        <span>Layanan: <strong class="text-white">{{ setting('office_hours') }}</strong></span>
                    </div>
                @endif
            </div>

            <!-- Theme Toggle Inside Mobile Menu -->
            <div class="pt-4 border-t border-white/10 mt-3">
                <button type="button" onclick="toggleTheme()" class="w-full min-h-[44px] flex items-center justify-between px-3.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-medium transition">
                    <span class="flex items-center gap-2">
                        <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                        <i data-lucide="sun" class="w-4 h-4 hidden dark:inline text-amber-400"></i>
                        <span>Mode Tampilan</span>
                    </span>
                    <span class="text-xs text-slate-400 dark:hidden">Ganti ke Gelap</span>
                    <span class="text-xs text-amber-300 hidden dark:inline">Ganti ke Terang</span>
                </button>
            </div>
        </div>
    </div>
</nav>

