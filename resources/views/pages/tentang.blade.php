@extends('layouts.app')

@section('title', 'Tentang DPD JULEHA Kabupaten Brebes – Profil, Visi, Misi & Sejarah')
@section('meta_description', 'Profil lengkap DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes. Mengenal visi, misi, sejarah pendirian, legalitas SK, dan nilai-nilai perjuangan sembelih halal.')

@section('content')
<!-- ===================== HERO TENTANG ===================== -->
<section class="relative py-20" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-dark/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4" data-aos="fade-up">
            <i data-lucide="info" class="w-3.5 h-3.5"></i>
            Profil Organisasi
        </span>
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-white mb-4" data-aos="fade-up" data-aos-delay="100">
            Tentang DPD JULEHA Kabupaten Brebes
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-base sm:text-lg leading-relaxed" data-aos="fade-up" data-aos-delay="200">
            Mengenal komitmen, landasan hukum, visi, misi, dan peran strategis DPD Juru Sembelih Halal di wilayah Kabupaten Brebes.
        </p>
    </div>
</section>

<!-- ===================== PROFIL & SEJARAH ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <span class="section-tag mb-4">
                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                    Latar Belakang
                </span>
                <h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-slate-900 dark:text-white mb-6 leading-tight">
                    Dedikasi untuk Kepastian Daging Halal Warga Brebes
                </h2>
                <div class="prose dark:prose-invert text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed space-y-4">
                    <p>
                        Kabupaten Brebes dengan populasi masyarakat muslim yang besar serta intensitas konsumsi dan kegiatan penyembelihan kurban yang tinggi, memerlukan ketersediaan juru sembelih yang memahami fiqih secara mendalam dan terampil dalam praktik teknis.
                    </p>
                    <p>
                        DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes hadir sebagai wadah berhimpun para pegiat, juru sembelih tradisional, panitia masjid, dan praktisi pemotongan hewan untuk meningkatkan standar keahlian bersama demi mewujudkan daging yang halal dan thayyib.
                    </p>
                </div>

                <div class="mt-8 p-6 rounded-2xl bg-brand/10 dark:bg-brand/20 border border-brand/20 dark:border-brand-dark/20" id="legalitas">
                    <h3 class="font-heading font-bold text-base text-brand dark:text-brand-dark mb-2 flex items-center gap-2">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                        Legalitas &amp; Surat Keputusan (SK)
                    </h3>
                    <ul class="space-y-1.5 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                        <li><strong>Surat Keputusan (SK):</strong> {{ setting('sk_legalitas', 'AHU-0000118.AH.01.07.TAHUN 2019') }}</li>
                        @if(setting('office_address'))
                            <li><strong>Alamat Sekretariat:</strong> {{ setting('office_address') }}</li>
                        @endif
                        <li><strong>Status Badan Hukum:</strong> Terdaftar Resmi Kemenkumham RI</li>
                        <li><strong>Wilayah Kerja:</strong> 17 Kecamatan di Kabupaten Brebes</li>
                    </ul>
                </div>
            </div>

            <!-- Kartu Prinsip -->
            <div data-aos="fade-left">
                <div class="bg-gradient-to-br from-brand-hero to-slate-950 text-white p-8 sm:p-10 rounded-3xl shadow-xl relative overflow-hidden">
                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="shield" class="w-7 h-7 text-brand-highlight"></i>
                    </div>

                    <h3 class="font-heading font-extrabold text-2xl mb-4">
                        Amanah Fiqih &amp; Kesejahteraan Hewan
                    </h3>

                    <p class="text-slate-200 text-sm sm:text-base leading-relaxed mb-6">
                        Setiap proses sembelih diawali dengan niat ibadah dan ketaatan kepada syariat. DPD JULEHA Brebes menolak keras praktik yang menyiksa hewan, penggunaan pisau tumpul, serta kelalaian dalam pemutusan saluran nafas, makanan, dan pembuluh darah.
                    </p>

                    <div class="space-y-3 border-t border-white/20 pt-6 text-sm">
                        <div class="flex items-center gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-brand-highlight"></i>
                            <span>Menjaga keabsahan ibadah kurban umat</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-brand-highlight"></i>
                            <span>Mengedukasi teknik asah bilah prima</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-brand-highlight"></i>
                            <span>Membina juru sembelih masjid berkelanjutan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== VISI & MISI ===================== -->
<section id="visi-misi" class="section-padding bg-slate-50 dark:bg-slate-800/40 border-y border-gray-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Arah Langkah"
            tagIcon="compass"
            title="Visi & Misi"
            highlight="Organisasi"
            subtitle="Panduan cita-cita dan langkah nyata DPD JULEHA Kabupaten Brebes dalam berkhidmat untuk masyarakat."
        />

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Visi Card -->
            <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-soft" data-aos="fade-up">
                <div class="w-12 h-12 bg-brand/10 dark:bg-brand/20 rounded-xl flex items-center justify-center mb-6 text-brand dark:text-brand-dark">
                    <i data-lucide="eye" class="w-6 h-6"></i>
                </div>
                <h3 class="font-heading font-extrabold text-2xl text-slate-900 dark:text-white mb-4">
                    Visi
                </h3>
                <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-gray-200/50 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-base leading-relaxed font-medium">
                    {{ setting('vision', 'Terwujudnya tata kelola dan juru sembelih halal yang profesional, kompeten, dan beradab di Kabupaten Brebes.') }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                    * Rumusan visi disepakati dalam musyawarah daerah kepengurusan DPD JULEHA Kabupaten Brebes.
                </p>
            </div>

            <!-- Misi Card -->
            <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-soft" data-aos="fade-up" data-aos-delay="100">
                <div class="w-12 h-12 bg-brand-accent-light dark:bg-brand-accent/20 rounded-xl flex items-center justify-center mb-6 text-brand-accent dark:text-brand-dark-accent">
                    <i data-lucide="target" class="w-6 h-6"></i>
                </div>
                <h3 class="font-heading font-extrabold text-2xl text-slate-900 dark:text-white mb-4">
                    Misi
                </h3>
                @php
                    $missionData = [];
                    if (!empty($settings['mission'])) {
                        $decoded = json_decode($settings['mission'], true);
                        if (is_array($decoded)) {
                            $missionData = array_map(fn($item) => is_array($item) ? ($item['point'] ?? '') : (string)$item, $decoded);
                        } else {
                            $missionData = array_filter(array_map('trim', explode("\n", str_replace(["\r\n", "\r"], "\n", $settings['mission']))));
                        }
                    }
                @endphp

                @if (!empty($missionData))
                    <ul class="space-y-3 text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        @foreach ($missionData as $misi)
                            @if(filled($misi))
                                <li class="flex items-start gap-3">
                                    <i data-lucide="check-circle" class="w-5 h-5 text-brand dark:text-brand-dark mt-0.5 flex-shrink-0"></i>
                                    <span>{{ $misi }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <ul class="space-y-3 text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-brand-dark mt-0.5 flex-shrink-0"></i>
                            <span>Membina dan mengedukasi juru sembelih tentang fiqih penyembelihan syar’i Madzhab Syafi’i dan fatwa MUI.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-brand-dark mt-0.5 flex-shrink-0"></i>
                            <span>Menerapkan kaidah kesejahteraan hewan (animal welfare) dan higienitas penanganan daging ASUH.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-brand-dark mt-0.5 flex-shrink-0"></i>
                            <span>Menyelenggarakan bimbingan teknis dan fasilitasi uji kompetensi bersertifikat BNSP.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-brand-dark mt-0.5 flex-shrink-0"></i>
                            <span>Membangun sinergi dengan panitia kurban masjid, Rumah Pemotongan Hewan (RPH), dan dinas terkait.</span>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- ===================== NILAI-NILAI ORGANISASI ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Karakter Juleha"
            tagIcon="award"
            title="Nilai-Nilai Luhur"
            highlight="Juru Sembelih Halal"
            subtitle="Prinsip dasar yang dipegang teguh oleh setiap anggota DPD JULEHA Kabupaten Brebes."
        />

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card-juleha text-center" data-aos="fade-up">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark flex items-center justify-center mb-4">
                    <i data-lucide="book-open" class="w-7 h-7"></i>
                </div>
                <h4 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-2">Syar'i</h4>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Berpedoman penuh pada hukum syariat Islam mengenai syarat sah penyembelihan halal.
                </p>
            </div>

            <div class="card-juleha text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-accent-light dark:bg-brand-accent/20 text-brand-accent dark:text-brand-dark-accent flex items-center justify-center mb-4">
                    <i data-lucide="smile" class="w-7 h-7"></i>
                </div>
                <h4 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-2">Ihsan</h4>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Memperlakukan hewan sembelihan dengan kasih sayang dan tidak menyiksa fisik maupun psikisnya.
                </p>
            </div>

            <div class="card-juleha text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark flex items-center justify-center mb-4">
                    <i data-lucide="shield-check" class="w-7 h-7"></i>
                </div>
                <h4 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-2">Amanah</h4>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Menjaga kepercayaan mudhahhi (shohibul qurban) dan konsumen atas kehalalan daging.
                </p>
            </div>

            <div class="card-juleha text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-accent-light dark:bg-brand-accent/20 text-brand-accent dark:text-brand-dark-accent flex items-center justify-center mb-4">
                    <i data-lucide="sparkles" class="w-7 h-7"></i>
                </div>
                <h4 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-2">Higienis</h4>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Menerapkan kebersihan alat dan karkas demi menghasilkan daging yang aman, sehat, dan utuh.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
