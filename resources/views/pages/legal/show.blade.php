@extends('layouts.app')

@section('title', $document->title . ' – DPD JULEHA Kabupaten Brebes')
@section('meta_description', 'Dokumen resmi ' . $document->title . ' DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes. Berlaku sejak ' . ($document->effective_date?->format('d F Y') ?? 'sekarang') . ' versi ' . $document->version . '.')

@section('content')
<!-- ===================== HERO DOKUMEN HUKUM ===================== -->
<section class="relative py-14 sm:py-18 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wide mb-3 sm:mb-4">
            <i data-lucide="scale" class="w-3.5 h-3.5"></i> Dokumen Hukum &amp; Legalitas
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl md:text-5xl text-white mb-3 sm:mb-4 leading-tight">
            {{ $document->title }}
        </h1>
        <div class="inline-flex flex-wrap items-center justify-center gap-3 text-xs sm:text-sm text-slate-200 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/15">
            <span class="flex items-center gap-1.5 font-medium">
                <i data-lucide="calendar" class="w-4 h-4 text-brand-highlight"></i>
                Berlaku sejak {{ $document->effective_date?->translatedFormat('d F Y') ?? $document->effective_date?->format('d M Y') ?? '-' }}
            </span>
            <span class="text-white/40">&bull;</span>
            <span class="flex items-center gap-1.5 font-bold text-brand-highlight">
                <i data-lucide="tag" class="w-4 h-4"></i>
                Versi {{ $document->version }}
            </span>
            <span class="text-white/40">&bull;</span>
            <a href="{{ route('legal.history', $document->slug) }}" class="underline hover:text-white flex items-center gap-1 font-medium transition-colors">
                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                Riwayat Perubahan
            </a>
        </div>
    </div>
</section>

<!-- ===================== KONTEN DOKUMEN & NAVIGASI ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            <!-- Sidebar Navigasi Dokumen Hukum -->
            <aside class="lg:col-span-4 order-2 lg:order-1 space-y-6">
                <!-- Box Menu Dokumen Terbit -->
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <h2 class="font-heading font-bold text-base text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-accent"></i>
                        Dokumen Hukum Terbit
                    </h2>
                    <nav class="space-y-1.5">
                        @foreach($otherDocuments as $doc)
                            <a href="{{ route('legal.show', $doc->slug) }}"
                               class="flex items-center justify-between p-2.5 rounded-xl text-xs sm:text-sm font-medium transition-colors {{ $doc->slug === $document->slug ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-900 dark:text-purple-200 border border-purple-200 dark:border-purple-800' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white' }}">
                                <span class="line-clamp-1">{{ $doc->title }}</span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full {{ $doc->slug === $document->slug ? 'bg-purple-600 text-white font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    v{{ $doc->version }}
                                </span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-700">
                        <a href="{{ route('data-rights.create') }}" class="btn-primary w-full justify-center text-xs py-2.5 flex items-center gap-2">
                            <i data-lucide="shield-question" class="w-4 h-4"></i>
                            Ajukan Permintaan Hak Data
                        </a>
                    </div>
                </div>

                <!-- Box Bantuan Sekretariat -->
                <div class="p-6 rounded-2xl bg-purple-50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/50 text-xs text-slate-600 dark:text-slate-300 space-y-2">
                    <div class="font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                        <i data-lucide="help-circle" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                        Kontak Kepatuhan Privasi
                    </div>
                    <p class="leading-relaxed">
                        Pengurus atau penasihat hukum yang ingin meninjau naskah kebijakan atau mengajukan pertanyaan privasi dapat menghubungi:
                    </p>
                    <div class="pt-2 text-slate-700 dark:text-slate-200 font-medium">
                        <p>Sekretariat: {{ setting('site_name', 'DPD JULEHA Kabupaten Brebes') }}</p>
                        <p>Email: {{ setting('privacy_contact_email') ?: setting('admin_email', 'dpdjulehabrebesofficial@gmail.com') }}</p>
                    </div>
                </div>
            </aside>

            <!-- Main Document Content -->
            <article class="lg:col-span-8 order-1 lg:order-2">
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-700 shadow-soft">
                    <!-- Notice RANCANGAN / Berlakunya dokumen -->
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-3">
                        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-600 dark:text-amber-400"></i>
                        <div>
                            <strong>Pemberitahuan Transparansi:</strong> Seluruh naskah hukum dan kebijakan yang tercantum di halaman ini disusun berdasarkan perilaku aktual sistem portal DPD JULEHA Brebes dan merupakan rancangan keterbukaan publik yang secara berkala ditinjau oleh Dewan Pengurus Daerah.
                        </div>
                    </div>

                    <!-- Rendered Document Content -->
                    <div class="prose dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 text-sm sm:text-base leading-relaxed space-y-6">
                        {!! $document->content !!}
                    </div>

                    <!-- Footer of Document -->
                    <div class="mt-10 pt-6 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
                        <div>
                            Terakhir diperbarui: <strong>{{ $document->updated_at?->format('d M Y, H:i') ?? '-' }} WIB</strong>
                        </div>
                        <div>
                            <a href="{{ route('legal.history', $document->slug) }}" class="inline-flex items-center gap-1.5 font-semibold text-brand hover:underline">
                                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                Lihat Catatan Perubahan Versi Ini
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
