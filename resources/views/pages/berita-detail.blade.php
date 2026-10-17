@extends('layouts.app')

@section('title', ($post->title ?? 'Artikel') . ' – DPD JULEHA Brebes')
@section('meta_description', Str::limit($post->summary ?? 'Artikel edukasi sembelih halal DPD JULEHA Kabupaten Brebes.', 160))

@section('content')
<!-- ===================== HERO DETAIL BERITA ===================== -->
<section class="relative py-20" style="background: var(--hero-gradient);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4">
            {{ $post->category ?? 'Edukasi Halal' }}
        </span>
        <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-5xl text-white leading-tight mb-4">
            {{ $post->title }}
        </h1>
        <div class="flex flex-wrap items-center justify-center gap-4 text-xs sm:text-sm text-slate-300">
            <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4"></i> {{ $post->published_at ? $post->published_at->translatedFormat('d F Y') : '-' }}</span>
            <span>&bull;</span>
            <span class="flex items-center gap-1.5"><i data-lucide="user" class="w-4 h-4"></i> {{ $post->author_name ?? 'Admin JULEHA Brebes' }}</span>
        </div>
    </div>
</section>

<!-- ===================== KONTEN ARTIKEL ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 text-base sm:text-lg leading-relaxed space-y-6">
            {!! nl2br(e($post->body ?? $post->content)) !!}
        </div>

        <!-- Tags -->
        <div class="flex flex-wrap gap-2 pt-8 mt-10 border-t border-gray-100 dark:border-slate-800">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 self-center mr-2">Topik:</span>
            <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-full">
                #{{ $post->category ?? 'JULEHA' }}
            </span>
            <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-full">
                #Brebes
            </span>
            <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-full">
                #SembelihHalal
            </span>
        </div>

        <!-- Box Profil Redaksi / DPD -->
        <div class="mt-12 p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-gray-200 dark:border-slate-700 flex flex-col sm:flex-row items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-brand text-white flex items-center justify-center font-bold text-xl flex-shrink-0">
                JB
            </div>
            <div>
                <h4 class="font-heading font-bold text-base text-slate-800 dark:text-white">Tim Redaksi &amp; Fatwa DPD JULEHA Brebes</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Artikel ini disusun dan disosialisasikan oleh Bidang Pendidikan, Fatwa &amp; Humas DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes untuk kepentingan dakwah dan literasi kehalalan umat.
                </p>
            </div>
        </div>

        @if (isset($related) && $related->isNotEmpty())
        <!-- Artikel Terkait -->
        <div class="mt-14 pt-10 border-t border-gray-100 dark:border-slate-800">
            <h3 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white mb-6">Artikel Terkait</h3>
            <div class="grid sm:grid-cols-3 gap-6">
                @foreach ($related as $rel)
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700">
                    <span class="text-xs font-bold text-brand dark:text-brand-dark block mb-2">{{ $rel->category }}</span>
                    <h5 class="font-heading font-bold text-sm text-slate-800 dark:text-white line-clamp-2 hover:text-brand dark:hover:text-brand-dark transition-colors mb-2">
                        <a href="{{ route('berita.show', $rel->slug) }}">{{ $rel->title }}</a>
                    </h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3">{{ $rel->summary }}</p>
                    <a href="{{ route('berita.show', $rel->slug) }}" class="text-xs font-semibold text-brand-accent dark:text-brand-dark-accent flex items-center gap-1">
                        Baca <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Kembali ke Daftar Berita -->
        <div class="text-center mt-12">
            <a href="{{ route('berita.index') }}" class="btn-outline text-slate-800 dark:text-white border-slate-300 dark:border-slate-700 text-sm py-2.5 px-6">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Daftar Berita
            </a>
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
