@extends('layouts.app')

@section('title', 'Riwayat Perubahan Versi: ' . $document->title . ' – DPD JULEHA Brebes')
@section('meta_description', 'Riwayat versi dan catatan pembaruan dokumen hukum ' . $document->title . ' DPD JULEHA Kabupaten Brebes.')

@section('content')
<!-- ===================== HERO RIWAYAT DOKUMEN ===================== -->
<section class="relative py-12 sm:py-16 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <a href="{{ route('legal.show', $document->slug) }}" class="inline-flex items-center gap-1.5 text-xs text-brand-highlight hover:underline mb-3">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Dokumen Aktif
        </a>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl text-white mb-2 leading-tight">
            Riwayat Perubahan Versi
        </h1>
        <p class="text-slate-200 text-sm sm:text-base max-w-xl mx-auto">
            {{ $document->title }}
        </p>
    </div>
</section>

<!-- ===================== LIST RIWAYAT VERSI ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Versi Terbit Saat Ini -->
        <div class="mb-10 p-6 sm:p-8 rounded-3xl bg-purple-50 dark:bg-purple-950/40 border-2 border-purple-300 dark:border-purple-700">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-purple-600 text-white rounded-full text-xs font-bold uppercase tracking-wider">
                        Versi {{ $document->version }} (Aktif)
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Berlaku sejak {{ $document->effective_date?->translatedFormat('d F Y') ?? $document->effective_date?->format('d M Y') ?? '-' }}
                    </span>
                </div>
                <a href="{{ route('legal.show', $document->slug) }}" class="btn-primary text-xs py-1.5 px-3">
                    Baca Dokumen Ini
                </a>
            </div>
            <h2 class="font-heading font-bold text-lg text-slate-900 dark:text-white mb-2">
                Catatan Perubahan Versi Ini
            </h2>
            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-white/70 dark:bg-slate-800/80 p-4 rounded-xl border border-purple-200 dark:border-purple-800">
                {{ $document->change_summary ?: 'Penerbitan awal dokumen hukum resmi.' }}
            </p>
        </div>

        <!-- Arsip Versi Terdahulu -->
        <h3 class="font-heading font-bold text-xl text-slate-800 dark:text-white mb-6 flex items-center gap-2">
            <i data-lucide="archive" class="w-5 h-5 text-brand-accent"></i>
            Arsip Versi Terdahulu
        </h3>

        @if($versions->isEmpty())
            <div class="p-8 text-center rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 text-sm">
                <i data-lucide="check-circle" class="w-8 h-8 mx-auto text-emerald-500 mb-2"></i>
                <p>Dokumen ini belum memiliki arsip versi lama (saat ini merupakan versi pertama yang terbit).</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($versions as $version)
                    <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <span class="font-mono font-bold text-sm text-slate-700 dark:text-slate-200 bg-slate-200 dark:bg-slate-700 px-2.5 py-1 rounded-lg">
                                Versi {{ $version->version }}
                            </span>
                            <span class="text-xs text-slate-500">
                                Berlaku: {{ $version->effective_date?->translatedFormat('d F Y') ?? '-' }}
                                @if($version->published_at)
                                    &bull; Diarsipkan: {{ $version->published_at->format('d M Y') }}
                                @endif
                            </span>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            <strong>Ringkasan:</strong> {{ $version->change_summary ?: 'Tidak ada ringkasan tertulis.' }}
                        </div>

                        <!-- Accordion Tampilkan Teks Versi Ini -->
                        <details class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs">
                            <summary class="cursor-pointer font-semibold text-brand hover:underline">
                                Tampilkan Teks Lengkap Versi {{ $version->version }}
                            </summary>
                            <div class="mt-3 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 leading-relaxed prose dark:prose-invert max-w-none text-xs">
                                {!! $version->content !!}
                            </div>
                        </details>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
