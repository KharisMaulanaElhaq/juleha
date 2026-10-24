@extends('layouts.app')

@section('title', 'DPD JULEHA Kabupaten Brebes – Juru Sembelih Halal')
@section('meta_description', 'Situs resmi DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes. Pusat informasi bimbingan teknis, sertifikasi juru sembelih, dan edukasi sembelih syar’i & higienis.')

@section('content')
<!-- ===================== HERO SECTION ===================== -->
<section id="beranda" class="hero-section flex items-center justify-center relative overflow-hidden">
    <!-- Ambient Lights (Soft & Clean) -->
    <div class="absolute top-20 right-16 w-80 h-80 bg-brand/[0.08] rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-40 left-10 w-72 h-72 bg-brand-accent/[0.04] rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 lg:py-28 relative z-10 w-full">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- Left Content -->
            <div>
                <!-- Badges / Tagline (Mobile: 1 baris ringkas, Desktop: 2 pill) -->
                <div class="mb-4 sm:mb-6" data-aos="fade-up">
                    <div class="sm:hidden inline-flex items-center gap-1.5 text-xs text-brand-highlight bg-white/10 border border-white/20 rounded-full px-3 py-1 font-medium backdrop-blur-sm">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-brand-highlight flex-shrink-0"></i>
                        <span>Syar'i &bull; Halal &bull; Kab. Brebes</span>
                    </div>
                    <div class="hidden sm:flex flex-wrap gap-2">
                        <span class="hero-badge">
                            <i data-lucide="award" class="w-4 h-4 text-brand-highlight"></i>
                            Syar'i &bull; Halal &bull; Higienis
                        </span>
                        <span class="hero-badge border-brand-accent/50 bg-brand-accent/20 text-brand-highlight">
                            <i data-lucide="map-pin" class="w-4 h-4 text-brand-dark-accent"></i>
                            Kabupaten Brebes
                        </span>
                    </div>
                </div>

                <h1 class="font-heading font-extrabold text-[28px] xs:text-3xl sm:text-4xl lg:text-5xl text-white leading-tight mb-3 sm:mb-6" data-aos="fade-up" data-aos-delay="100">
                    Juru Sembelih Halal<br />
                    <span class="text-brand-highlight">Amanah &amp; Profesional</span>
                </h1>

                <!-- Mobile: Ringkas 3 baris -->
                <p class="sm:hidden text-slate-200 text-sm leading-relaxed mb-6 line-clamp-3" data-aos="fade-up" data-aos-delay="200">
                    Membina juru sembelih agar sesuai syariat, memperhatikan perikehewanan, dan menghasilkan daging yang aman serta higienis di Kabupaten Brebes.
                </p>
                <!-- Desktop: Lengkap -->
                <p class="hidden sm:block text-slate-200 text-base sm:text-lg leading-relaxed mb-8 max-w-xl" data-aos="fade-up" data-aos-delay="200">
                    Dewan Pengurus Daerah Juru Sembelih Halal (DPD JULEHA) Kabupaten Brebes berkomitmen mengedukasi dan membina para juru sembelih agar proses penyembelihan hewan memenuhi rukun syariat Islam, memperhatikan perikehewanan, dan menghasilkan daging yang aman serta higienis.
                </p>

                <!-- CTA Actions (Mobile: 1 tombol full-width + link teks di bawahnya) -->
                <div class="sm:hidden space-y-3 mb-8" data-aos="fade-up" data-aos-delay="300">
                    @if($hasOpenEvents ?? false)
                        <a href="{{ route('bimtek.index') }}" class="btn-primary w-full justify-center text-sm py-3.5 shadow-lg flex items-center gap-2">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                            <span>Daftar Bimtek Sekarang</span>
                        </a>
                    @else
                        <a href="{{ route('bimtek.index') }}" class="btn-primary w-full justify-center text-sm py-3.5 shadow-lg flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <span>Lihat Jadwal Kegiatan</span>
                        </a>
                    @endif
                    <div class="text-center">
                        <a href="{{ route('program.index') }}" class="text-xs text-slate-300 hover:text-white underline underline-offset-4 font-medium transition inline-flex items-center gap-1">
                            <span>Pelajari Program Kerja</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>

                <!-- Desktop CTA: Tombol berdampingan -->
                <div class="hidden sm:flex flex-wrap gap-4 mb-10" data-aos="fade-up" data-aos-delay="300">
                    @if($hasOpenEvents ?? false)
                        <a href="{{ route('bimtek.index') }}" class="btn-primary text-base py-3.5 px-7">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                            <span>Daftar Bimtek Sekarang</span>
                        </a>
                    @else
                        <a href="{{ route('bimtek.index') }}" class="btn-primary text-base py-3.5 px-7">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                            <span>Lihat Jadwal Kegiatan</span>
                        </a>
                    @endif
                    <a href="{{ route('program.index') }}" class="btn-outline text-base py-3.5 px-7">
                        <i data-lucide="compass" class="w-5 h-5"></i>
                        <span>Pelajari Program</span>
                    </a>
                </div>

                <!-- Stats Snippet (Mobile: 3 kolom bersih tanpa border tebal) -->
                <div class="grid grid-cols-3 gap-2 sm:gap-8 pt-4 border-t border-white/10 text-center sm:text-left sm:flex sm:flex-wrap" data-aos="fade-up" data-aos-delay="400">
                    <div>
                        <div class="font-heading font-extrabold text-xl sm:text-3xl text-brand-highlight" data-count="17">17</div>
                        <div class="text-slate-300 text-[11px] sm:text-sm mt-0.5 leading-tight">Kecamatan</div>
                    </div>
                    <div class="sm:border-l sm:border-white/20 sm:pl-6 sm:pl-8">
                        <div class="font-heading font-extrabold text-xl sm:text-3xl text-white" data-count="100" data-suffix="+">100+</div>
                        <div class="text-slate-300 text-[11px] sm:text-sm mt-0.5 leading-tight">Juru Sembelih</div>
                    </div>
                    <div class="sm:border-l sm:border-white/20 sm:pl-6 sm:pl-8">
                        <div class="font-heading font-extrabold text-xl sm:text-3xl text-white">100%</div>
                        <div class="text-slate-300 text-[11px] sm:text-sm mt-0.5 leading-tight">Standar Syar'i</div>
                    </div>
                </div>
            </div>

            <!-- Right Graphic / Neutral Card -->
            <div class="hidden lg:flex justify-center relative" data-aos="fade-left" data-aos-delay="200">
                <div class="relative w-full max-w-md">
                    <!-- Geometric Illustration Box -->
                    <div class="bg-gradient-to-br from-brand-hero/70 to-slate-950 p-8 rounded-3xl border border-white/15 shadow-2xl backdrop-blur-md">
                        <div class="w-16 h-16 rounded-2xl bg-brand-accent flex items-center justify-center mb-6 shadow-lg shadow-brand-accent/30">
                            <i data-lucide="shield-check" class="w-8 h-8 text-slate-900"></i>
                        </div>

                        <h3 class="font-heading font-bold text-2xl text-white mb-3">
                            Amanah Halal Untuk Umat
                        </h3>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">
                            Menjamin setiap hewan yang disembelih terputus 3 saluran vital (hulkum, mari', dan wadajain) sesuai sunnah Rasulullah SAW dengan perlakuan ihsan tanpa kekerasan.
                        </p>

                        <div class="space-y-3 border-t border-white/15 pt-5 text-sm text-slate-200">
                            <div class="flex items-center gap-3">
                                <i data-lucide="check" class="w-4 h-4 text-brand-dark"></i>
                                <span>Bimbingan Fiqih Madzhab Syafi'i &amp; Fatwa MUI</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="check" class="w-4 h-4 text-brand-dark"></i>
                                <span>Kaidah Kesejahteraan Hewan (Animal Welfare)</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="check" class="w-4 h-4 text-brand-dark"></i>
                                <span>Standar Daging ASUH (Aman, Sehat, Utuh, Halal)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Badges -->
                    <div class="absolute -top-5 -left-6 bg-white dark:bg-slate-800 rounded-xl px-4 py-3 shadow-xl border border-gray-100 dark:border-slate-700 flex items-center gap-3">
                        <div class="w-9 h-9 bg-brand/10 dark:bg-brand/20 rounded-lg flex items-center justify-center">
                            <i data-lucide="award" class="w-5 h-5 text-brand dark:text-brand-dark"></i>
                        </div>
                        <div>
                            <div class="font-heading font-bold text-xs text-slate-800 dark:text-white">Standar Kompetensi</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">SKKNI Juru Sembelih Halal</div>
                        </div>
                    </div>

                    <div class="absolute -bottom-5 -right-6 bg-white dark:bg-slate-800 rounded-xl px-4 py-3 shadow-xl border border-gray-100 dark:border-slate-700 flex items-center gap-3">
                        <div class="w-9 h-9 bg-brand-accent-light dark:bg-brand-accent/15 rounded-lg flex items-center justify-center">
                            <i data-lucide="calendar" class="w-5 h-5 text-brand-accent dark:text-brand-dark-accent"></i>
                        </div>
                        <div>
                            <div class="font-heading font-bold text-xs text-slate-800 dark:text-white">Bimtek Terjadwal</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Terbuka Untuk DKM &amp; Umum</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CTA BANNER BIMTEK (Desktop only, agar tidak duplikat di HP) ===================== -->
<div class="hidden md:block">
    <x-cta variant="banner" />
</div>

<!-- ===================== RINGKASAN PROFIL DPD ===================== -->
<section id="profil-singkat" class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left Info Graphic Placeholder -->
            <div data-aos="fade-right">
                <div class="bg-gradient-to-br from-brand-light/70 to-white dark:from-[#1a1330] dark:to-slate-800 p-6 sm:p-8 rounded-3xl border border-brand/10 dark:border-brand-dark/20">
                    <span class="section-tag mb-4">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        Profil Organisasi
                    </span>
                    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white mb-4 leading-tight">
                        Wadah Sinergi Juru Sembelih Halal di Brebes
                    </h2>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed mb-6">
                        DPD JULEHA Kabupaten Brebes didirikan sebagai respons atas kebutuhan masyarakat akan ketersediaan daging sembelihan yang terjamin kehalalannya, baik untuk konsumsi harian di pasar dan RPH maupun momentum ibadah kurban tahunan.
                    </p>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="bg-white dark:bg-slate-800/80 p-4 rounded-xl border border-gray-100 dark:border-slate-700">
                            <div class="text-xs text-slate-500 dark:text-slate-400">Legalitas &amp; SK</div>
                            <div class="font-heading font-bold text-sm text-brand dark:text-brand-dark mt-1">{{ setting('sk_legalitas', 'AHU-0000118.AH.01.07.TAHUN 2019') }}</div>
                        </div>
                        <div class="bg-white dark:bg-slate-800/80 p-4 rounded-xl border border-gray-100 dark:border-slate-700">
                            <div class="text-xs text-slate-500 dark:text-slate-400">Status Legalitas</div>
                            <div class="font-heading font-bold text-sm text-brand-accent dark:text-brand-dark-accent mt-1">Kemenkumham RI</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('tentang') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand dark:text-brand-dark hover:gap-3 transition-all">
                            <span>Baca Selengkapnya Tentang DPD JULEHA Brebes</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Key Pillars -->
            <div data-aos="fade-left">
                <span class="section-tag-accent mb-4">
                    <i data-lucide="star" class="w-3.5 h-3.5"></i>
                    Empat Nilai Utama
                </span>
                <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white mb-6 leading-tight">
                    Komitmen Menjaga Kehalalan dari Hulu ke Hilir
                </h2>

                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-lg bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark flex items-center justify-center flex-shrink-0 font-bold">
                            1
                        </div>
                        <div>
                            <h4 class="font-heading font-bold text-slate-800 dark:text-white text-base">Kepatuhan Syariah Murni</h4>
                            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-1">Mengutamakan syarat sah sembelihan dan doa tasmiyah untuk menjamin status halal hewan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-lg bg-brand-accent-light dark:bg-brand-accent/20 text-brand-accent dark:text-brand-dark-accent flex items-center justify-center flex-shrink-0 font-bold">
                            2
                        </div>
                        <div>
                            <h4 class="font-heading font-bold text-slate-800 dark:text-white text-base">Prinsip Ihsan &amp; Kesejahteraan Hewan</h4>
                            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-1">Perlakuan lemah lembut, bilah tajam prima, dan tidak menakut-nakuti hewan sebelum pemotongan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-lg bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark flex items-center justify-center flex-shrink-0 font-bold">
                            3
                        </div>
                        <div>
                            <h4 class="font-heading font-bold text-slate-800 dark:text-white text-base">Higiene &amp; Sanitasi Pangan</h4>
                            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-1">Penanganan karkas bersih dan terpisah dari kotoran guna menghasilkan daging ASUH berkualitas.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-lg bg-brand-accent-light dark:bg-brand-accent/20 text-brand-accent dark:text-brand-dark-accent flex items-center justify-center flex-shrink-0 font-bold">
                            4
                        </div>
                        <div>
                            <h4 class="font-heading font-bold text-slate-800 dark:text-white text-base">Kompetensi Teruji (SKKNI)</h4>
                            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-1">Standarisasi keahlian juru sembelih melalui pelatihan terstruktur dan uji kompetensi berkala.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== PROGRAM KERJA UNGGULAN ===================== -->
<section id="program-unggulan" class="section-padding bg-slate-50 dark:bg-slate-800/40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Program Kerja"
            tagIcon="layers"
            title="Program Kerja Unggulan"
            highlight="DPD JULEHA Brebes"
            subtitle="Berbagai program terpadu untuk membina, meningkatkan keahlian, dan melayani kebutuhan kehalalan hewan sembelihan di Kabupaten Brebes."
        />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
            @foreach ($programs as $prog)
            <div class="card-juleha group flex flex-col justify-between" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-brand/10 dark:bg-brand/20 rounded-xl flex items-center justify-center text-brand dark:text-brand-dark group-hover:bg-brand group-hover:text-white transition-colors">
                            <i data-lucide="{{ $prog->icon ?? 'check-circle' }}" class="w-6 h-6"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand-accent dark:text-brand-dark-accent">
                            {{ $prog->category }}
                        </span>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-2 leading-snug group-hover:text-brand dark:group-hover:text-brand-dark transition-colors">
                        {{ $prog->title }}
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-4">
                        {{ Str::limit($prog->summary, 110) }}
                    </p>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between">
                    <a href="{{ route('program.show', $prog->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-accent dark:text-brand-dark-accent group-hover:gap-2.5 transition-all">
                        <span>Pelajari Rincian</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('program.index') }}" class="btn-primary text-sm py-3 px-8 inline-flex items-center gap-2">
                <span>Lihat Seluruh Program Kerja</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===================== JADWAL BIMTEK TERDEKAT ===================== -->
<section id="jadwal-bimtek" class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Agenda Pelatihan"
            tagIcon="calendar"
            title="Jadwal Bimbingan Teknis"
            highlight="Terdekat"
            subtitle="Ikuti agenda pelatihan dan bimbingan teknis terdekat untuk meningkatkan keahlian sembelih halal Anda."
        />

        <div class="grid lg:grid-cols-2 gap-8">
            @forelse ($events as $event)
            @php
                $canRegister = $event->canRegister();
                $seatsLeft = $event->seatsLeft();
                $isFull = $event->isFull();
            @endphp
            <div class="p-6 sm:p-8 rounded-2xl border border-gray-200 dark:border-slate-700 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 shadow-soft" data-aos="fade-up">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark px-3 py-1 rounded-full">
                        {{ $event->type?->label() ?? 'Bimtek' }}
                    </span>
                    @if($canRegister)
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Pendaftaran Dibuka</span>
                        </span>
                    @elseif($isFull)
                        <span class="text-xs text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1.5 bg-amber-100 dark:bg-amber-950/50 px-2.5 py-0.5 rounded-full">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Kuota Penuh</span>
                        </span>
                    @else
                        <span class="text-xs text-slate-600 dark:text-slate-400 font-bold flex items-center gap-1.5 bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 rounded-full">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <span>{{ $event->registrationStateLabel() }}</span>
                        </span>
                    @endif
                </div>

                <h3 class="font-heading font-extrabold text-xl text-slate-800 dark:text-white mb-3">
                    {{ $event->title }}
                </h3>

                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed mb-6">
                    {{ Str::limit($event->description, 130) }}
                </p>

                <div class="space-y-2.5 py-4 border-y border-gray-100 dark:border-slate-700 text-sm mb-6">
                    <div class="flex items-center gap-2.5 text-slate-700 dark:text-slate-300">
                        <i data-lucide="calendar" class="w-4 h-4 text-brand dark:text-brand-dark flex-shrink-0"></i>
                        <span><strong>Tanggal:</strong> {{ $event->starts_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-slate-700 dark:text-slate-300">
                        <i data-lucide="map-pin" class="w-4 h-4 text-brand-accent dark:text-brand-dark-accent flex-shrink-0"></i>
                        <span><strong>Lokasi:</strong> {{ $event->location }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-slate-700 dark:text-slate-300">
                        <i data-lucide="users" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                        @if(is_null($seatsLeft))
                            <span><strong>Sisa Kuota:</strong> Kuota Terbuka</span>
                        @elseif($seatsLeft <= 5 && $seatsLeft > 0)
                            <span class="text-amber-600 dark:text-amber-400 font-bold animate-pulse"><strong>Sisa Kuota:</strong> Tinggal {{ $seatsLeft }} kursi!</span>
                        @elseif($seatsLeft === 0)
                            <span class="text-rose-600 dark:text-rose-400 font-semibold"><strong>Sisa Kuota:</strong> Kuota Penuh</span>
                        @else
                            <span><strong>Sisa Kuota:</strong> Sisa {{ $seatsLeft }} dari {{ $event->quota }} kursi</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        Infaq: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $event->fee > 0 ? 'Rp ' . number_format($event->fee, 0, ',', '.') : 'Gratis / Infaq' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('bimtek.show', $event->slug) }}" class="inline-flex items-center text-xs py-2 px-3.5 rounded-lg font-semibold font-heading border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition">
                            Detail
                        </a>
                        @if($canRegister)
                            <a href="{{ route('bimtek.daftar', $event->slug) }}" class="btn-primary text-xs py-2.5 px-4 flex items-center gap-1.5">
                                <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                                <span>Daftar Bimtek</span>
                            </a>
                        @else
                            <a href="{{ setting_wa_link('Halo Panitia DPD JULEHA Brebes, saya ingin bertanya tentang kegiatan ' . $event->title) }}" target="_blank" rel="noopener" class="btn-outline text-xs py-2 px-3 text-emerald-600 border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 inline-flex items-center gap-1.5">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Tanya Panitia</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 px-6 rounded-3xl border border-dashed border-gray-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 space-y-4" data-aos="fade-up">
                <div class="w-16 h-16 rounded-full bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark flex items-center justify-center mx-auto">
                    <i data-lucide="calendar-check" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="font-heading font-extrabold text-xl text-slate-800 dark:text-white">
                        Belum Ada Jadwal Bimtek Terdekat
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                        Agenda bimtek sebelumnya telah selesai diselenggarakan. Pantau terus informasi jadwal gelombang berikutnya melalui website atau hubungi pengurus kami.
                    </p>
                </div>
                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ setting_wa_link('Halo Pengurus DPD JULEHA Brebes, saya ingin menanyakan jadwal bimtek berikutnya.') }}" target="_blank" rel="noopener" class="btn-primary text-xs py-2.5 px-4 inline-flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Tanya Jadwal via WhatsApp</span>
                    </a>
                    <a href="{{ route('program.index') }}" class="inline-flex items-center gap-2 text-xs py-2.5 px-4 rounded-lg font-semibold font-heading border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700 hover:border-slate-400 dark:hover:border-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-xs">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Lihat Program Pelatihan</span>
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ===================== COUNTER STATISTIK ===================== -->
<section class="py-10 sm:py-16 bg-brand-hero text-white border-y border-purple-900/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-aos="zoom-in" data-aos-delay="0">
                <div class="font-heading font-extrabold text-3xl sm:text-4xl text-brand-highlight" data-count="{{ $settings['counter_districts'] ?? '17' }}">{{ $settings['counter_districts'] ?? '17' }}</div>
                <div class="text-slate-300 text-xs sm:text-sm mt-1 uppercase tracking-wide">Kecamatan Terjangkau</div>
            </div>
            <div data-aos="zoom-in" data-aos-delay="100">
                <div class="font-heading font-extrabold text-3xl sm:text-4xl text-white" data-count="{{ $settings['counter_juleha'] ?? '250' }}" data-suffix="+">{{ $settings['counter_juleha'] ?? '250' }}+</div>
                <div class="text-slate-300 text-xs sm:text-sm mt-1 uppercase tracking-wide">Juru Sembelih Terlatih</div>
            </div>
            <div data-aos="zoom-in" data-aos-delay="200">
                <div class="font-heading font-extrabold text-3xl sm:text-4xl text-brand-dark-accent" data-count="{{ $settings['counter_rph'] ?? '8' }}" data-suffix="+">{{ $settings['counter_rph'] ?? '8' }}+</div>
                <div class="text-slate-300 text-xs sm:text-sm mt-1 uppercase tracking-wide">Mitra RPH / TPH</div>
            </div>
            <div data-aos="zoom-in" data-aos-delay="300">
                <div class="font-heading font-extrabold text-3xl sm:text-4xl text-white" data-count="{{ $settings['counter_bimtek'] ?? '12' }}" data-suffix="+">{{ $settings['counter_bimtek'] ?? '12' }}+</div>
                <div class="text-slate-300 text-xs sm:text-sm mt-1 uppercase tracking-wide">Bimtek Terselenggara</div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FAQ SINGKAT BERANDA ===================== -->
<section id="faq-singkat" class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Tanya Jawab"
            tagIcon="help-circle"
            title="Pertanyaan yang Sering"
            highlight="Diajukan"
            subtitle="Ringkasan jawaban seputar peran juru sembelih halal dan keikutsertaan bimbingan teknis di Brebes."
        />

        <div class="space-y-0" data-aos="fade-up">
            @forelse ($faqs as $f)
                <x-faq-item
                    :question="$f->question"
                    :answer="$f->answer"
                    :isOpen="$loop->first"
                />
            @empty
                <x-faq-item
                    question="Apa itu JULEHA dan apa tujuannya di Kabupaten Brebes?"
                    answer="JULEHA adalah singkatan dari Juru Sembelih Halal. DPD JULEHA Kabupaten Brebes bertujuan memberikan bimbingan teknis dan sertifikasi agar proses penyembelihan hewan halal di Brebes memenuhi syariat Islam, standar higiene pangan, dan kesejahteraan hewan."
                    :isOpen="true"
                />
                <x-faq-item
                    question="Siapa saja yang boleh mengikuti pelatihan atau bimbingan teknis JULEHA?"
                    answer="Pelatihan terbuka bagi pengurus takmir masjid, panitia kurban, pekerja RPH/RPU, peternak, pedagang hewan, serta masyarakat umum muslim yang bertekad memahami tata cara penyembelihan halal yang benar."
                />
            @endforelse
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('faq') }}" class="inline-flex items-center justify-center text-sm py-2.5 px-6 rounded-lg font-semibold font-heading border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                Lihat Seluruh Pertanyaan (FAQ)
            </a>
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA AKHIR ===================== -->
<x-cta variant="strip" />
@endsection
