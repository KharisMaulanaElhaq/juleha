@extends('layouts.app')

@section('title', ($program->meta_title ?: $program->title) . ' – DPD JULEHA Brebes')
@section('meta_description', $program->meta_description ?: $program->summary)

@section('content')
<!-- ===================== HERO DETAIL PROGRAM ===================== -->
<section class="relative min-h-[60vh] flex items-center" style="background: var(--hero-gradient);">
    <div class="absolute top-20 right-16 w-80 h-80 bg-brand/[0.08] rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-20 left-10 w-72 h-72 bg-brand-accent/[0.04] rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 relative z-10 w-full">
        <div class="grid lg:grid-cols-12 gap-10 items-center">
            <!-- Left Info -->
            <div class="lg:col-span-8">
                <div class="flex flex-wrap items-center gap-2 mb-4" data-aos="fade-up">
                    <span class="inline-flex items-center gap-1.5 bg-brand/20 text-brand-highlight border border-brand-dark/30 text-xs font-bold px-3.5 py-1.5 rounded-full uppercase tracking-wide">
                        <i data-lucide="{{ $program->icon ?? 'award' }}" class="w-3.5 h-3.5 text-brand-highlight"></i>
                        {{ $program->category }}
                    </span>
                    @if(!empty($program->lsp_name))
                    <span class="inline-flex items-center gap-1.5 bg-amber-400/20 text-amber-200 border border-amber-300/30 text-xs font-semibold px-3 py-1.5 rounded-full">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-300"></i>
                        {{ $program->lsp_name }}
                    </span>
                    @endif
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-white leading-tight mb-6" data-aos="fade-up" data-aos-delay="100">
                    {{ $program->title }}
                </h1>

                <p class="text-slate-200 text-base sm:text-lg leading-relaxed mb-8 max-w-2xl" data-aos="fade-up" data-aos-delay="200">
                    {{ $program->summary }}
                </p>

                <div class="flex flex-wrap items-center gap-4" data-aos="fade-up" data-aos-delay="300">
                    @if($program->cta_type === 'bimtek')
                    <a href="{{ route('bimtek.index') }}" class="btn-primary text-base py-3.5 px-7">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        <span>Daftar Bimtek Sekarang</span>
                    </a>
                    <a href="{{ setting_wa_link($program->getDefaultWhatsAppMessage()) }}" target="_blank" rel="noopener noreferrer"
                       class="bg-white/10 hover:bg-white/20 text-white font-heading font-semibold text-base py-3.5 px-6 rounded-xl border border-white/20 transition-all flex items-center gap-2 backdrop-blur-sm">
                        <i data-lucide="message-circle" class="w-5 h-5 text-emerald-400"></i>
                        <span>Tanya via WhatsApp</span>
                    </a>
                    @else
                    <a href="{{ $program->getCtaUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="bg-emerald-600 hover:bg-emerald-700 text-white font-heading font-bold text-base py-3.5 px-8 rounded-xl shadow-lg shadow-emerald-950/40 transition-all flex items-center gap-2.5">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        <span>{{ $program->getResolvedCtaText() }}</span>
                    </a>
                    <a href="#rincian-program" class="btn-outline text-base py-3.5 px-7">
                        <i data-lucide="info" class="w-5 h-5"></i>
                        <span>Pelajari Rincian</span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Right Graphic / Icon Box -->
            <div class="hidden lg:flex lg:col-span-4 justify-center" data-aos="fade-left" data-aos-delay="200">
                <div class="w-full max-w-sm bg-gradient-to-br from-brand-hero/80 to-slate-950 rounded-3xl border border-purple-800/40 p-8 shadow-2xl backdrop-blur-md text-center">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-brand/20 border border-brand/30 flex items-center justify-center mb-6 text-brand-highlight shadow-inner">
                        <i data-lucide="{{ $program->icon ?? 'award' }}" class="w-10 h-10"></i>
                    </div>
                    <div class="text-xs font-bold uppercase tracking-wider text-brand-accent dark:text-brand-dark-accent mb-1">
                        {{ $program->category }}
                    </div>
                    <h3 class="font-heading font-bold text-xl text-white mb-3">
                        DPD JULEHA Brebes
                    </h3>
                    <p class="text-slate-300 text-xs leading-relaxed border-t border-white/10 pt-4">
                        Menjamin tata cara pemotongan hewan syar'i, higienis, dan terstandar untuk ketenteraman umat.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== DETAIL KONTEN UTAMA ===================== -->
<section id="rincian-program" class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-12">
            <!-- Left 8 Cols: Penjelasan Lengkap, Keunggulan, Alur -->
            <div class="lg:col-span-8 space-y-12">
                <!-- Deskripsi Lengkap -->
                <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-gray-100 dark:border-slate-800 p-6 sm:p-8 shadow-soft" data-aos="fade-up">
                    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand/10 dark:bg-brand/20 flex items-center justify-center text-brand dark:text-brand-dark flex-shrink-0">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <span>Penjelasan Lengkap Program</span>
                    </h2>

                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 text-base leading-relaxed space-y-4">
                        {!! $program->description !!}
                    </div>
                </div>

                <!-- Keunggulan / Cakupan Fitur -->
                @if(!empty($program->features))
                <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-gray-100 dark:border-slate-800 p-6 sm:p-8" data-aos="fade-up">
                    <h3 class="font-heading font-bold text-xl text-slate-900 dark:text-white mb-6 flex items-center gap-2.5">
                        <i data-lucide="check-circle" class="w-6 h-6 text-emerald-500"></i>
                        <span>Poin Keunggulan & Cakupan Layanan</span>
                    </h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach($program->features as $feat)
                        @php $featText = is_array($feat) ? ($feat['feature'] ?? reset($feat)) : $feat; @endphp
                        <div class="flex items-start gap-3 bg-white dark:bg-slate-800 p-4 rounded-xl border border-gray-100 dark:border-slate-700/80 shadow-sm">
                            <i data-lucide="check" class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                            <span class="text-slate-700 dark:text-slate-300 text-sm leading-snug">{{ $featText }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Persyaratan Mengikuti (Hanya tampil jika ada isinya) -->
                @if(!empty($program->requirements))
                <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-gray-100 dark:border-slate-800 p-6 sm:p-8 shadow-soft" data-aos="fade-up">
                    <h3 class="font-heading font-bold text-xl text-slate-900 dark:text-white mb-6 flex items-center gap-2.5">
                        <i data-lucide="clipboard-list" class="w-6 h-6 text-brand dark:text-brand-dark"></i>
                        <span>Persyaratan Program</span>
                    </h3>

                    <ul class="space-y-3">
                        @foreach($program->requirements as $req)
                        @php $reqText = is_array($req) ? ($req['requirement'] ?? reset($req)) : $req; @endphp
                        @if(!empty($reqText))
                        <li class="flex items-start gap-3 text-slate-700 dark:text-slate-300 text-sm">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-brand-accent dark:text-brand-dark-accent flex-shrink-0 mt-1"></i>
                            <span>{{ $reqText }}</span>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Alur / Cara Mengikuti (Hanya tampil jika ada isinya) -->
                @if(!empty($program->how_to_join))
                <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-gray-100 dark:border-slate-800 p-6 sm:p-8 shadow-soft" data-aos="fade-up">
                    <h3 class="font-heading font-bold text-xl text-slate-900 dark:text-white mb-6 flex items-center gap-2.5">
                        <i data-lucide="git-merge" class="w-6 h-6 text-brand dark:text-brand-dark"></i>
                        <span>Alur & Cara Mengikuti</span>
                    </h3>

                    <div class="space-y-4">
                        @foreach($program->how_to_join as $index => $step)
                        @php $stepText = is_array($step) ? ($step['step'] ?? reset($step)) : $step; @endphp
                        @if(!empty($stepText))
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-800">
                            <div class="w-8 h-8 rounded-full bg-brand text-white font-heading font-bold text-sm flex items-center justify-center flex-shrink-0">
                                {{ $index + 1 }}
                            </div>
                            <div class="text-slate-700 dark:text-slate-300 text-sm sm:text-base leading-relaxed pt-1">
                                {{ $stepText }}
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right 4 Cols: Sidebar Kontak & Program Terkait -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Card Kontak Cepat -->
                <div class="card-juleha p-6 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-soft sticky top-28" data-aos="fade-left">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                        <i data-lucide="phone-call" class="w-6 h-6"></i>
                    </div>

                    <h4 class="font-heading font-bold text-lg text-slate-900 dark:text-white mb-2">
                        Konsultasi & Layanan
                    </h4>
                    <p class="text-slate-600 dark:text-slate-400 text-xs leading-relaxed mb-5">
                        Hubungi sekretariat DPD JULEHA Kabupaten Brebes untuk informasi jadwal, permohonan narasumber, atau fasilitasi program.
                    </p>

                    <div class="space-y-3 mb-6 text-xs text-slate-700 dark:text-slate-300 border-t border-b border-gray-100 dark:border-slate-800 py-4">
                        @if(setting('office_hours'))
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="clock" class="w-4 h-4 text-brand dark:text-brand-dark flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-semibold block text-slate-800 dark:text-slate-200">Jam Layanan:</span>
                                <span>{{ setting('office_hours') }}</span>
                            </div>
                        </div>
                        @endif

                        @if(setting('whatsapp_number'))
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="message-square" class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-semibold block text-slate-800 dark:text-slate-200">WhatsApp Resmi:</span>
                                <span>{{ setting_wa_display() }}</span>
                            </div>
                        </div>
                        @endif

                        @if(setting('admin_email'))
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="mail" class="w-4 h-4 text-brand-accent dark:text-brand-dark-accent flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-semibold block text-slate-800 dark:text-slate-200">Email:</span>
                                <span class="break-all">{{ setting('admin_email') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-2.5">
                        @if($program->cta_type === 'bimtek')
                        <a href="{{ route('bimtek.index') }}" class="btn-primary w-full justify-center text-xs py-3">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <span>Daftar Bimtek Sekarang</span>
                        </a>
                        <a href="{{ setting_wa_link($program->getDefaultWhatsAppMessage()) }}" target="_blank" rel="noopener noreferrer"
                           class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors w-full shadow-sm">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Tanya via WhatsApp</span>
                        </a>
                        @else
                        <a href="{{ $program->getCtaUrl() }}" target="_blank" rel="noopener noreferrer"
                           class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors w-full shadow-sm">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>{{ $program->getResolvedCtaText() }}</span>
                        </a>
                        @endif
                    </div>

                    @if(!empty($otherPrograms) && $otherPrograms->count() > 0)
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-slate-800">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">
                            Program Lainnya
                        </div>
                        <div class="space-y-2">
                            @foreach($otherPrograms as $op)
                            <a href="{{ route('program.show', $op->slug) }}"
                               class="group flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300 group-hover:text-brand dark:group-hover:text-brand-dark transition-colors">
                                    {{ $op->title }}
                                </span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand transition-colors"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CTA STRIP ===================== -->
<div class="bg-brand-hero py-6 border-t border-purple-900/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-white text-center sm:text-left">
            <span class="w-3 h-3 bg-brand-highlight rounded-full animate-pulse flex-shrink-0"></span>
            <span class="font-heading font-semibold text-sm sm:text-base">
                Butuh layanan atau informasi seputar {{ $program->title }}?
            </span>
        </div>
        <a href="{{ $program->getCtaUrl() }}" target="_blank" rel="noopener noreferrer"
           class="bg-brand-accent hover:bg-brand-accent-hover text-slate-900 font-heading font-bold px-6 py-2.5 rounded-lg transition-all text-sm flex items-center gap-2 whitespace-nowrap shadow-md">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>{{ $program->getResolvedCtaText() }}</span>
        </a>
    </div>
</div>
@endsection
