@extends('layouts.app')

@section('title', 'Berita, Artikel & Edukasi Halal – DPD JULEHA Brebes')
@section('meta_description', 'Kumpulan artikel fiqih sembelih halal, tips asah bilah, panduan kurban, dan warta kegiatan DPD JULEHA Kabupaten Brebes.')

@section('content')
<!-- ===================== HERO BERITA ===================== -->
<section class="relative py-10 sm:py-16 md:py-20 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wide mb-3 sm:mb-4" data-aos="fade-up">
            <i data-lucide="newspaper" class="w-3.5 h-3.5"></i>
            Warta &amp; Edukasi
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl md:text-5xl text-white mb-3 sm:mb-4 leading-tight" data-aos="fade-up" data-aos-delay="100">
            Berita &amp; Artikel JULEHA Brebes
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base md:text-lg leading-relaxed line-clamp-3 sm:line-clamp-none" data-aos="fade-up" data-aos-delay="200">
            Kajian fiqih sembelih, teknik ketajaman bilah, panduan ibadah kurban, serta informasi terkini dari DPD JULEHA Brebes.
        </p>
    </div>
</section>

<!-- ===================== ARTIKEL GRID ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main News List -->
            <div class="lg:col-span-2 space-y-8">
                @forelse ($posts as $post)
                <div class="berita-card p-6 sm:p-8 bg-slate-50 dark:bg-slate-800" data-aos="fade-up">
                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mb-3">
                        <span class="bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark font-bold px-2.5 py-0.5 rounded-full">
                            {{ $post->category ?? 'Warta Halal' }}
                        </span>
                        <span>&bull;</span>
                        <span>{{ $post->published_at ? $post->published_at->translatedFormat('d F Y') : '-' }}</span>
                        <span>&bull;</span>
                        <span>{{ $post->author_name ?? 'Admin Juleha' }}</span>
                    </div>

                    <h2 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-800 dark:text-white mb-3 hover:text-brand dark:hover:text-brand-dark transition-colors">
                        <a href="{{ route('berita.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>

                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed mb-6">
                        {{ $post->summary }}
                    </p>

                    <a href="{{ route('berita.show', $post->slug) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-accent dark:text-brand-dark-accent hover:gap-2.5 transition-all">
                        <span>Baca Artikel Lengkap</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @empty
                <div class="text-center py-16 bg-slate-50 dark:bg-slate-800 rounded-3xl p-8 border border-gray-100 dark:border-slate-700">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark flex items-center justify-center">
                        <i data-lucide="newspaper" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-heading font-bold text-xl text-slate-800 dark:text-white mb-2">Belum Ada Artikel Dipublikasikan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
                        Artikel edukasi, kajian fiqih, dan warta kegiatan akan segera diperbarui secara berkala oleh redaksi DPD JULEHA Brebes.
                    </p>
                </div>
                @endforelse
            </div>

            <!-- Sidebar -->
            <aside class="space-y-6">
                <!-- Kategori Box -->
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700">
                    <h3 class="font-heading font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-brand dark:text-brand-dark"></i>
                        Topik Edukasi
                    </h3>
                    <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li class="flex items-center justify-between p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700">
                            <span>Fiqih Sembelih Syar'i</span>
                            <span class="text-xs bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark font-bold px-2 py-0.5 rounded-full">Kajian</span>
                        </li>
                        <li class="flex items-center justify-between p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700">
                            <span>Teknik Asah &amp; Bilah</span>
                            <span class="text-xs bg-brand-accent-light dark:bg-brand-hero-dark text-brand-accent dark:text-brand-dark-accent font-bold px-2 py-0.5 rounded-full">Praktik</span>
                        </li>
                        <li class="flex items-center justify-between p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700">
                            <span>Panduan Hewan Kurban</span>
                            <span class="text-xs bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark font-bold px-2 py-0.5 rounded-full">Iduladha</span>
                        </li>
                        <li class="flex items-center justify-between p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700">
                            <span>Daging ASUH &amp; Higiene</span>
                            <span class="text-xs bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold px-2 py-0.5 rounded-full">Kesehatan</span>
                        </li>
                    </ul>
                </div>

                <!-- Info Kontak Bantuan -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-brand-hero to-brand-hero-dark text-white">
                    <h3 class="font-heading font-bold text-lg mb-2">Konsultasi Sembelih Halal</h3>
                    <p class="text-xs text-slate-200 leading-relaxed mb-4">
                        Punya pertanyaan seputar hukum fiqih sembelih atau ingin mengundang narasumber DPD JULEHA Brebes?
                    </p>
                    <a href="{{ route('kontak') }}" class="btn-accent text-xs py-2.5 px-4 w-full justify-center">
                        Hubungi Sekretariat
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
