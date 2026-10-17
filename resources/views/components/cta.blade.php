@props([
    'variant' => 'strip', // 'strip' or 'banner'
    'title' => 'Ingin Menjadi Juru Sembelih Halal Bersertifikat?',
    'subtitle' => 'Ikuti Bimbingan Teknis dan Uji Kompetensi JULEHA Kabupaten Brebes.',
    'phone' => null,
    'phoneLink' => route('bimtek.index'),
    'buttonText' => null,
])

@php
    if ($buttonText === null) {
        $hasAnyOpen = \App\Models\Event::where('status', 'open')->get()->filter(fn ($e) => $e->canRegister())->isNotEmpty();
        $buttonText = $hasAnyOpen ? 'Daftar Bimtek Sekarang' : 'Lihat Jadwal Kegiatan';
    }
@endphp

@if($variant === 'banner')
    <div class="bg-brand-hero dark:bg-brand-hero-dark py-4 border-b border-purple-900/60 dark:border-purple-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-white">
                    <span class="w-3 h-3 bg-brand-highlight rounded-full animate-pulse"></span>
                    <span class="font-heading font-semibold text-base">{{ $title }}</span>
                    @if($subtitle)
                        <span class="text-brand-highlight text-sm hidden md:block">{{ $subtitle }}</span>
                    @endif
                </div>
                <a href="{{ $phoneLink }}"
                    class="bg-brand-accent hover:bg-brand-accent-hover text-slate-900 font-heading font-bold px-6 py-2.5 rounded-lg transition-all text-sm whitespace-nowrap flex items-center gap-2 shadow-md">
                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                    {{ $buttonText }}
                </a>
            </div>
        </div>
    </div>
@else
    <div style="background: linear-gradient(90deg, #1f1335 0%, #33204f 50%, #1f1335 100%);" class="py-6 border-y border-purple-950/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white/15 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse border border-white/20">
                        <i data-lucide="award" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <div class="font-heading font-bold text-white text-lg sm:text-xl">{{ $title }}</div>
                        <div class="text-brand-highlight text-sm mt-0.5">{{ $subtitle }}</div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2.5 sm:gap-3">
                    <a href="{{ $phoneLink }}"
                        class="bg-brand-accent hover:bg-brand-accent-hover text-slate-900 font-heading font-bold px-6 py-3 rounded-xl transition-all text-sm flex items-center justify-center gap-2 shadow-lg min-h-[44px] w-full sm:w-auto">
                        <i data-lucide="calendar" class="w-4 h-4"></i> {{ $buttonText }}
                    </a>
                    <a href="{{ route('kontak') }}"
                        class="bg-white/10 border border-white/25 text-white font-heading font-semibold px-6 py-3 rounded-xl hover:bg-white/20 transition-all text-sm flex items-center justify-center gap-2 min-h-[44px] w-full sm:w-auto">
                        <i data-lucide="message-circle" class="w-4 h-4"></i> Konsultasi Panitia Kurban
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
