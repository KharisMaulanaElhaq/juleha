@extends('layouts.app')

@section('title', 'Program Kerja – DPD JULEHA Kabupaten Brebes')
@section('meta_description', 'Program kerja resmi DPD Juru Sembelih Halal Kabupaten Brebes: layanan aqiqah, pendampingan qurban, syiar edukasi keliling, bimtek pelatihan, dan fasilitasi sertifikasi kompetensi BNSP.')

@section('content')
<!-- ===================== HERO PROGRAM ===================== -->
<section class="relative py-10 sm:py-16 md:py-20 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-dark/30 text-xs font-semibold px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wide mb-3 sm:mb-4" data-aos="fade-up">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            Khidmat & Pelayanan Umat
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl md:text-5xl text-white mb-3 sm:mb-4 leading-tight" data-aos="fade-up" data-aos-delay="100">
            Program Kerja DPD JULEHA Brebes
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base md:text-lg leading-relaxed line-clamp-3 sm:line-clamp-none" data-aos="fade-up" data-aos-delay="200">
            Inisiatif terpadu untuk memastikan pelaksanaan penyembelihan hewan di Kabupaten Brebes senantiasa syar'i, amanah, beradab, dan terstandar kompetensinya.
        </p>
    </div>
</section>

<!-- ===================== KATEGORI 1: LAYANAN MASYARAKAT ===================== -->
<section id="layanan-masyarakat" class="section-padding bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Khidmat Langsung"
            tagIcon="heart"
            title="Layanan Masyarakat"
            highlight="& Ibadah"
            subtitle="Pelayanan dan pendampingan penyembelihan halal langsung bagi masyarakat, panitia masjid, pondok pesantren, dan instansi di Kabupaten Brebes."
        />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
            @foreach ($communityPrograms as $prog)
            <div class="card-juleha flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-14 h-14 bg-brand/10 dark:bg-brand/20 rounded-2xl flex items-center justify-center text-brand dark:text-brand-dark group-hover:bg-brand group-hover:text-white transition-colors">
                            <i data-lucide="{{ $prog->icon ?? 'heart' }}" class="w-7 h-7"></i>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 px-3 py-1 rounded-full">
                            {{ $prog->category }}
                        </span>
                    </div>

                    <h3 class="font-heading font-bold text-xl text-slate-800 dark:text-white mb-3 group-hover:text-brand dark:group-hover:text-brand-dark transition-colors">
                        {{ $prog->title }}
                    </h3>

                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-5">
                        {{ $prog->summary }}
                    </p>

                    @if(!empty($prog->features))
                    <ul class="space-y-2 mb-6 border-t border-gray-100 dark:border-slate-800/80 pt-4">
                        @foreach(array_slice($prog->features, 0, 3) as $feat)
                        @php $featText = is_array($feat) ? ($feat['feature'] ?? reset($feat)) : $feat; @endphp
                        <li class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                            <span>{{ $featText }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex flex-col sm:flex-row gap-2.5">
                    <a href="{{ $prog->getCtaUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 transition-colors shadow-sm flex-1">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>{{ $prog->getResolvedCtaText() }}</span>
                    </a>
                    <a href="{{ route('program.show', $prog->slug) }}"
                       class="btn-outline text-xs py-2.5 px-4 justify-center">
                        <span>Rincian</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===================== KATEGORI 2: PENDIDIKAN & SERTIFIKASI ===================== -->
<section id="pendidikan-sertifikasi" class="section-padding bg-slate-50 dark:bg-slate-800/40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-section-header
            tag="Keahlian & Lisensi"
            tagIcon="graduation-cap"
            title="Pendidikan & Sertifikasi"
            highlight="Kompetensi"
            subtitle="Bimbingan teknis berkala, pengkaderan pelatih, dan fasilitasi uji sertifikasi profesi BNSP berstandar nasional bersama LSP mitra."
        />

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach ($educationPrograms as $prog)
            <div class="card-juleha flex flex-col justify-between group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-brand/10 dark:bg-brand/20 rounded-xl flex items-center justify-center text-brand dark:text-brand-dark group-hover:bg-brand group-hover:text-white transition-colors">
                            <i data-lucide="{{ $prog->icon ?? 'book-open' }}" class="w-6 h-6"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider bg-brand/10 dark:bg-brand/20 text-brand dark:text-brand-dark border border-brand/20 px-2.5 py-0.5 rounded-full">
                            {{ $prog->cta_type === 'bimtek' ? 'Pelatihan' : 'Sertifikasi' }}
                        </span>
                    </div>

                    <h3 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-2 leading-snug group-hover:text-brand dark:group-hover:text-brand-dark transition-colors">
                        {{ $prog->title }}
                    </h3>

                    @if(!empty($prog->lsp_name))
                    <div class="mb-3">
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 px-2.5 py-0.5 rounded-md">
                            <i data-lucide="shield-check" class="w-3 h-3"></i>
                            {{ $prog->lsp_name }}
                        </span>
                    </div>
                    @endif

                    <p class="text-slate-600 dark:text-slate-400 text-xs leading-relaxed mb-4">
                        {{ $prog->summary }}
                    </p>

                    @if(!empty($prog->features))
                    <ul class="space-y-1.5 mb-5 border-t border-gray-100 dark:border-slate-800/80 pt-3">
                        @foreach(array_slice($prog->features, 0, 2) as $feat)
                        @php $featText = is_array($feat) ? ($feat['feature'] ?? reset($feat)) : $feat; @endphp
                        <li class="flex items-start gap-1.5 text-[11px] text-slate-600 dark:text-slate-300">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-brand dark:text-brand-dark flex-shrink-0 mt-0.5"></i>
                            <span>{{ $featText }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>

                <div class="pt-3 border-t border-gray-100 dark:border-slate-800 flex flex-col gap-2">
                    @if($prog->cta_type === 'bimtek')
                    <a href="{{ route('bimtek.index') }}"
                       class="btn-primary text-xs py-2 px-3 w-full justify-center">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Daftar Bimtek</span>
                    </a>
                    @else
                    <a href="{{ $prog->getCtaUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        <span>{{ $prog->getResolvedCtaText() }}</span>
                    </a>
                    @endif
                    <a href="{{ route('program.show', $prog->slug) }}"
                       class="btn-outline text-xs py-2 px-3 w-full justify-center text-slate-600 dark:text-slate-300">
                        <span>Lihat Rincian</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
