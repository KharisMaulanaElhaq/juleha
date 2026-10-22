@extends('layouts.app')

@section('title', 'Tanya Jawab (FAQ) – DPD JULEHA Kabupaten Brebes')
@section('meta_description', 'Pertanyaan yang sering diajukan mengenai bimbingan teknis, sertifikasi juru sembelih halal, hukum fiqih sembelih, dan program DPD JULEHA Brebes.')

@section('content')
<!-- ===================== HERO FAQ ===================== -->
<section class="relative py-20" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4" data-aos="fade-up">
            <i data-lucide="help-circle" class="w-3.5 h-3.5"></i> Pusat Informasi
        </span>
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-white mb-4" data-aos="fade-up" data-aos-delay="100">
            Pertanyaan yang Sering Diajukan (FAQ)
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-base sm:text-lg leading-relaxed mb-8" data-aos="fade-up" data-aos-delay="200">
            Temukan jawaban lengkap seputar keanggotaan, pendaftaran bimtek, sertifikasi profesi, dan tata cara sembelih syar'i bersama DPD JULEHA Brebes.
        </p>

        <!-- Search -->
        <div class="max-w-md mx-auto" data-aos="fade-up" data-aos-delay="300">
            <div class="relative">
                <input type="text" id="faq-search" placeholder="Cari pertanyaan seputar JULEHA..."
                    class="w-full bg-white/10 border border-white/25 text-white placeholder-slate-300 rounded-full px-5 py-3 pl-12 text-sm focus:outline-none focus:border-brand-highlight transition-all"
                    oninput="filterFAQs()" />
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300"></i>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FAQ CONTENT ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $categoryLabels = [
                'umum' => 'Umum & Keorganisasian',
                'bimtek' => 'Bimbingan Teknis & Pelatihan',
                'fiqih' => 'Fiqih & Praktik Penyembelihan',
                'sertifikasi' => 'Sertifikasi Kompetensi BNSP & Kemitraan Masjid',
            ];
        @endphp

        <!-- Category Tabs -->
        @if ($faqs->isNotEmpty())
        <div class="flex flex-wrap justify-center gap-2.5 mb-12" data-aos="fade-up">
            @foreach ($faqs as $catKey => $catItems)
                <button type="button" onclick="scrollToCategory('cat-{{ Str::slug($catKey) }}')"
                    class="px-5 py-2 {{ $loop->even ? 'bg-brand-accent-light dark:bg-brand-hero-dark text-brand-accent dark:text-brand-dark-accent border-brand-accent/20 dark:border-brand-dark-accent/20' : 'bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark border-brand/20 dark:border-brand-dark/20' }} border rounded-full text-xs sm:text-sm font-semibold hover:opacity-80 transition-opacity">
                    {{ $categoryLabels[$catKey] ?? ucfirst($catKey) }}
                </button>
            @endforeach
        </div>
        @endif

        @forelse ($faqs as $catKey => $catItems)
        <div id="cat-{{ Str::slug($catKey) }}" class="mb-14">
            <h2 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white mb-6 flex items-center gap-2" data-aos="fade-up">
                <span class="w-1.5 h-6 {{ $loop->even ? 'bg-brand-accent dark:bg-brand-dark-accent' : 'bg-brand dark:bg-brand-dark' }} rounded-full"></span>
                {{ $categoryLabels[$catKey] ?? ucfirst($catKey) }}
            </h2>
            <div class="space-y-0" data-aos="fade-up">
                @foreach ($catItems as $index => $item)
                <x-faq-item
                    :question="$item->question"
                    :answer="$item->answer"
                    :isOpen="$loop->parent->first && $index === 0"
                />
                @endforeach
            </div>
        </div>
        @empty
        <div class="text-center py-16 bg-slate-50 dark:bg-slate-800 rounded-3xl p-8 border border-gray-100 dark:border-slate-700">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark flex items-center justify-center">
                <i data-lucide="help-circle" class="w-8 h-8"></i>
            </div>
            <h3 class="font-heading font-bold text-xl text-slate-800 dark:text-white mb-2">Belum Ada FAQ Tersedia</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
                Daftar pertanyaan dan jawaban seputar bimbingan teknis serta fatwa halal akan segera diperbarui.
            </p>
        </div>
        @endforelse
    </div>
</section>

<!-- ===================== SCRIPT FAQ FILTER ===================== -->
@push('scripts')
<script>
    function scrollToCategory(id) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function filterFAQs() {
        const q = (document.getElementById('faq-search')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.faq-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(q) ? '' : 'none';
        });
    }
</script>
@endpush

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
