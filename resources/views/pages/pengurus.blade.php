@extends('layouts.app')

@section('title', 'Susunan Pengurus DPD JULEHA Kabupaten Brebes ' . ($boardPeriod ?? '2024-2029'))
@section('meta_description', 'Susunan resmi pengurus DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes masa bakti ' . ($boardPeriod ?? '2024-2029') . '. Dewan Pelindung, Pembina, Pengurus Harian, dan Bidang-Bidang Kepengurusan.')

@push('head')
    <!-- Schema.org Organization Structured Data (Tanpa Data Pribadi Pengurus) -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'DPD JULEHA Kabupaten Brebes',
        'url' => url('/'),
        'logo' => asset('assets/logo-juleha-brebes.png'),
        'description' => 'Susunan pengurus resmi Dewan Pengurus Daerah Juru Sembelih Halal Kabupaten Brebes masa bakti ' . ($boardPeriod ?? '2024-2029') . '.',
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <style>
        @media (min-width: 768px) {
            .division-content {
                display: block !important;
            }
        }
    </style>
@endpush

@section('content')
<!-- ===================== HERO PENGURUS ===================== -->
<section class="relative py-14 sm:py-20 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-dark/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4" data-aos="fade-up">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Struktur Organisasi Resmi</span>
        </span>
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-white mb-3 tracking-tight" data-aos="fade-up" data-aos-delay="100">
            Susunan Pengurus
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-base sm:text-lg leading-relaxed font-medium" data-aos="fade-up" data-aos-delay="200">
            DPD JULEHA Kabupaten Brebes &middot; Masa bakti {{ $boardPeriod ?? '2024-2029' }}
        </p>
    </div>
</section>

<!-- ===================== BAGIAN 1: PELINDUNG & PEMBINA (LEMBAGA) ===================== -->
<section class="section-padding bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Kelembagaan"
            tagIcon="landmark"
            title="Pelindung dan"
            highlight="Pembina"
            subtitle="Unsur pimpinan daerah, instansi kedinasan peternakan, dan otoritas keagamaan di Kabupaten Brebes."
        />

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @forelse ($pelindungPembina as $instansi)
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-50 dark:bg-slate-800/80 border border-gray-200/80 dark:border-slate-700/80 shadow-soft text-center flex flex-col items-center justify-center transition-all hover:border-brand/30 dark:hover:border-brand-dark/30 hover:shadow-md" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
                    <!-- Ikon Seragam Lembaga (Bukan Foto / Inisial) -->
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-brand/10 dark:bg-brand/20 border-2 border-brand/20 dark:border-brand-dark/30 flex items-center justify-center text-brand dark:text-brand-dark mb-5 shadow-xs flex-shrink-0">
                        <i data-lucide="landmark" class="w-8 h-8 sm:w-10 sm:h-10"></i>
                    </div>

                    <!-- Kedudukan sebagai judul kecil -->
                    <span class="text-xs uppercase font-extrabold tracking-wider text-brand-accent dark:text-brand-dark-accent mb-2">
                        {{ $instansi->position ?? 'Pembina Lembaga' }}
                    </span>

                    <!-- Nama Lembaga sebagai teks utama -->
                    <h3 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-800 dark:text-white leading-snug">
                        {{ $instansi->name }}
                    </h3>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-slate-500 text-sm">
                    Data pelindung dan pembina belum tersedia.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ===================== BAGIAN 2: PENGURUS HARIAN (PERORANGAN) ===================== -->
<section class="section-padding bg-slate-50 dark:bg-slate-800/40 border-b border-gray-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Pimpinan Harian"
            tagIcon="users"
            title="Pengurus"
            highlight="Harian"
            subtitle="Pimpinan pelaksana harian DPD Juru Sembelih Halal Kabupaten Brebes."
        />

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($pimpinanHarian as $bph)
                <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-gray-200/80 dark:border-slate-700 shadow-soft text-center flex flex-col items-center justify-between transition-all hover:shadow-md" data-aos="fade-up" data-aos-delay="{{ $loop->index * 75 }}">
                    <div class="w-full flex flex-col items-center">
                        <!-- Foto atau Avatar Inisial Bersih Tanpa Gelar -->
                        @if($bph->photo)
                            <img src="{{ asset('storage/' . $bph->photo) }}"
                                 alt="{{ $bph->name }}"
                                 class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-brand/20 dark:border-brand-dark/30 shadow-md mb-4" />
                        @else
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-brand/10 dark:bg-brand/20 border-4 border-brand/20 dark:border-brand-dark/30 flex items-center justify-center text-brand dark:text-brand-dark font-heading font-black text-2xl sm:text-3xl mb-4 shadow-inner">
                                {{ $bph->initials }}
                            </div>
                        @endif

                        <!-- Label Jabatan -->
                        <span class="text-xs font-bold uppercase tracking-wider text-brand dark:text-brand-dark bg-brand/10 dark:bg-brand/20 px-3 py-1 rounded-full inline-block mb-3">
                            {{ $bph->position }}
                        </span>

                        <!-- Nama Lengkap Beserta Gelar -->
                        <h3 class="font-heading font-extrabold text-lg sm:text-xl text-slate-800 dark:text-white leading-snug">
                            {{ $bph->name }}
                        </h3>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-slate-500 text-sm">
                    Data pengurus harian belum tersedia.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ===================== BAGIAN 3: BIDANG-BIDANG ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Amanah Kerja"
            tagIcon="layers"
            title="Bidang-Bidang"
            highlight="Kepengurusan"
            subtitle="Tim kerja pelaksana teknis dakwah, pelatihan, keahlian, kemitraan, dan pelayanan masyarakat."
        />

        @php
            $divisionIcons = [
                'Bidang Pendidikan' => 'graduation-cap',
                "Bidang Da'wah" => 'book-open',
                'Bidang Pengembangan Program' => 'trending-up',
                'Bidang Tim Ahli' => 'stethoscope',
                'Bidang Usaha' => 'briefcase',
                'Bidang Humas' => 'megaphone',
                'Bidang Adat' => 'scale',
                'Bidang Perlengkapan/Umum' => 'package',
                'Bidang Sosial & Kebencanaan' => 'heart-handshake',
            ];
        @endphp

        <!-- Grid 2-3 kolom di laptop / Accordion di HP (360px) -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($bidangMembers as $divisionName => $membersInDivision)
                @php
                    $iconName = $divisionIcons[$divisionName] ?? 'award';
                    $isFirst = $loop->first;
                @endphp
                <div class="border border-gray-200 dark:border-slate-700/80 rounded-3xl bg-white dark:bg-slate-800 shadow-soft overflow-hidden transition-all hover:border-brand/30 dark:hover:border-brand-dark/30" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 6) * 50 }}">
                    <!-- Header Kartu Bidang: Interaktif toggle di HP, statis di laptop -->
                    <button type="button"
                            onclick="toggleDivisionAccordion(this)"
                            class="w-full text-left p-5 sm:p-6 flex items-center justify-between gap-4 cursor-pointer md:cursor-default focus:outline-none focus:ring-2 focus:ring-brand/40 bg-slate-50/70 dark:bg-slate-800/90 transition-colors"
                            aria-expanded="{{ $isFirst ? 'true' : 'false' }}">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark flex items-center justify-center flex-shrink-0 shadow-xs">
                                <i data-lucide="{{ $iconName }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-base sm:text-lg text-slate-800 dark:text-white leading-snug">
                                    {{ $divisionName }}
                                </h3>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    {{ count($membersInDivision) }} Anggota
                                </span>
                            </div>
                        </div>

                        <!-- Panah Accordion Khusus Layar HP -->
                        <div class="md:hidden w-8 h-8 rounded-full bg-slate-200/60 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 flex items-center justify-center flex-shrink-0 transition-transform duration-200 accordion-arrow {{ $isFirst ? 'rotate-180' : '' }}">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </button>

                    <!-- Daftar Anggota Bernomor (Buka otomatis di desktop, accordion di HP) -->
                    <div class="division-content border-t border-gray-100 dark:border-slate-700/80 p-5 sm:p-6 {{ $isFirst ? 'block' : 'hidden' }} md:!block">
                        <ol class="space-y-3">
                            @foreach ($membersInDivision as $member)
                                <li class="flex items-start gap-3 text-base text-slate-700 dark:text-slate-200 leading-relaxed">
                                    <span class="w-6 h-6 rounded-full bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark text-xs font-extrabold flex items-center justify-center flex-shrink-0 mt-0.5">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="font-medium text-slate-800 dark:text-slate-100">{{ $member->name }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-slate-500 text-sm">
                    Data bidang kepengurusan belum tersedia.
                </div>
            @endforelse
        </div>

        <!-- Catatan SK Penetapan (Hanya Tampil Jika Diisi di Pengaturan Situs) -->
        @if(filled($boardSkNote))
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-slate-800 text-center">
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 italic">
                    Berdasarkan {{ $boardSkNote }}
                </p>
            </div>
        @endif
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection

@push('scripts')
<script>
    function toggleDivisionAccordion(button) {
        // Pada layar tablet/desktop (>= 768px), konten selalu terbuka secara permanen
        if (window.innerWidth >= 768) {
            return;
        }

        const card = button.closest('div');
        const content = card.querySelector('.division-content');
        const arrow = button.querySelector('.accordion-arrow');
        const isHidden = content.classList.contains('hidden');

        if (isHidden) {
            content.classList.remove('hidden');
            content.classList.add('block');
            button.setAttribute('aria-expanded', 'true');
            if (arrow) arrow.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            content.classList.remove('block');
            button.setAttribute('aria-expanded', 'false');
            if (arrow) arrow.classList.remove('rotate-180');
        }
    }
</script>
@endpush
