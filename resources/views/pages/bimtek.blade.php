@extends('layouts.app')

@section('title', 'Jadwal Bimbingan Teknis (Bimtek) Juru Sembelih Halal Brebes')
@section('meta_description', 'Daftar jadwal bimbingan teknis (bimtek) dan pelatihan juru sembelih halal DPD JULEHA Kabupaten Brebes.')

@section('content')
<!-- ===================== HERO BIMTEK ===================== -->
<section class="relative py-10 sm:py-16 md:py-20 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wide mb-3 sm:mb-4" data-aos="fade-up">
            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
            Pelatihan &amp; Bimtek
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl md:text-5xl text-white mb-3 sm:mb-4 leading-tight" data-aos="fade-up" data-aos-delay="100">
            Jadwal Bimbingan Teknis JULEHA
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base md:text-lg leading-relaxed line-clamp-3 sm:line-clamp-none" data-aos="fade-up" data-aos-delay="200">
            Tingkatkan pemahaman fiqih sembelih, pengasahan bilah, dan teknik perlakuan ihsan terhadap hewan melalui bimtek terdekat di Kabupaten Brebes.
        </p>
    </div>
</section>

<!-- ===================== DAFTAR JADWAL BIMTEK ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Flash Alert Pesan Penolakan/Pengalihan -->
        @if(session('error'))
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-start gap-3 shadow-soft" role="alert">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <strong class="font-bold block mb-0.5">Pemberitahuan Pendaftaran</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @elseif(session('info'))
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 flex items-start gap-3 shadow-soft" role="alert">
                <i data-lucide="info" class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <strong class="font-bold block mb-0.5">Informasi</strong>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        <!-- BAGIAN 1: KEGIATAN YANG SEDANG DIBUKA -->
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200 dark:border-slate-800 pb-4">
                <div>
                    <h2 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Kegiatan yang Sedang Dibuka</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Pendaftaran sedang aktif dan kuota masih tersedia.
                    </p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 self-start sm:self-auto">
                    {{ count($openEvents ?? []) }} kegiatan tersedia
                </span>
            </div>

            <div class="space-y-6">
                @forelse ($openEvents ?? [] as $event)
                    @php
                        $seatsLeft = $event->seatsLeft();
                    @endphp
                    <div class="p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 shadow-soft grid lg:grid-cols-3 gap-8 items-center" data-aos="fade-up">
                        <div class="lg:col-span-2 space-y-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full {{ $event->type?->badgeClasses() ?? 'bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark' }}">
                                    {{ $event->type?->label() ?? 'Bimtek' }}
                                </span>
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Pendaftaran Dibuka</span>
                                </span>
                            </div>

                            <h3 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-800 dark:text-white leading-snug">
                                {{ $event->title }}
                            </h3>

                            <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed line-clamp-3">
                                {{ $event->description }}
                            </p>

                            <div class="grid sm:grid-cols-3 gap-3 pt-2 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="calendar" class="w-4 h-4 text-brand dark:text-brand-dark flex-shrink-0"></i>
                                    <span>{{ $event->starts_at->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4 text-brand dark:text-brand-dark flex-shrink-0"></i>
                                    <span>{{ $event->starts_at->format('H:i') }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-brand-accent dark:text-brand-dark-accent flex-shrink-0"></i>
                                    <span class="truncate">{{ $event->location }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Box -->
                        <div class="lg:border-l lg:border-gray-200 lg:dark:border-slate-700 lg:pl-8 flex flex-col justify-center gap-4">
                            <div class="bg-white dark:bg-slate-900/60 p-4 rounded-xl border border-gray-100 dark:border-slate-700 text-center">
                                <div class="text-xs text-slate-500 dark:text-slate-400">Infaq Kontribusi</div>
                                <div class="font-heading font-extrabold text-lg text-slate-800 dark:text-white mt-0.5">
                                    {{ $event->fee > 0 ? 'Rp ' . number_format($event->fee, 0, ',', '.') : 'Gratis / Infaq' }}
                                </div>
                                <div class="text-[12px] font-semibold mt-1">
                                    @if(is_null($seatsLeft))
                                        <span class="text-emerald-600 dark:text-emerald-400">Kuota Terbuka</span>
                                    @elseif($seatsLeft <= 5 && $seatsLeft > 0)
                                        <span class="text-amber-600 dark:text-amber-400 font-bold animate-pulse">Tinggal {{ $seatsLeft }} kursi!</span>
                                    @else
                                        <span class="text-brand dark:text-brand-dark font-medium">Sisa {{ $seatsLeft }} kursi</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <a href="{{ route('bimtek.daftar', $event->slug) }}" class="btn-primary w-full justify-center text-sm py-3 flex items-center gap-2 shadow-md">
                                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                                    <span>Daftar Sekarang</span>
                                </a>
                                <a href="{{ route('bimtek.show', $event->slug) }}" class="btn-outline w-full justify-center text-xs py-2 text-slate-700 dark:text-slate-200 border-gray-300 dark:border-slate-600">
                                    Rincian Materi &amp; Lokasi
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 rounded-3xl border border-dashed border-gray-300 dark:border-slate-700 p-8 text-slate-500 dark:text-slate-400 space-y-3">
                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-400"></i>
                        <p class="font-medium text-base text-slate-700 dark:text-slate-200">Belum ada kegiatan yang membuka pendaftaran baru saat ini.</p>
                        <p class="text-xs max-w-md mx-auto">Silakan hubungi sekretariat DPD JULEHA Brebes untuk informasi jadwal angkatan berikutnya.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- BAGIAN 2: KEGIATAN PENUH ATAU PENDAFTARAN DITUTUP -->
        @if(! empty($otherEvents) && count($otherEvents) > 0)
            <div class="space-y-6 pt-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200 dark:border-slate-800 pb-4">
                    <div>
                        <h2 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-600 dark:text-slate-400 flex items-center gap-2.5">
                            <i data-lucide="archive" class="w-5 h-5 text-slate-400"></i>
                            <span>Kegiatan Penuh atau Pendaftaran Ditutup</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Daftar kegiatan mendatang yang kuotanya telah terpenuhi atau pendaftarannya ditutup sementara.
                        </p>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 self-start sm:self-auto">
                        {{ count($otherEvents) }} kegiatan
                    </span>
                </div>

                <div class="space-y-6">
                    @foreach ($otherEvents as $event)
                        @php
                            $state = $event->registrationState();
                            $stateLabel = $event->registrationStateLabel();
                            $buttonLabel = $state->buttonLabel();
                            $lucideIcon = $state->lucideIcon();
                            $waLink = setting_wa_link('Halo Panitia DPD JULEHA Brebes, saya ingin bertanya tentang kegiatan ' . $event->title);
                        @endphp
                        <div class="p-6 sm:p-8 rounded-3xl border border-gray-200/80 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/50 opacity-90 shadow-xs grid lg:grid-cols-3 gap-8 items-center">
                            <div class="lg:col-span-2 space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                        {{ $event->type?->label() ?? 'Bimtek' }}
                                    </span>

                                    <!-- Badge Status Terhitung -->
                                    @if($state === \App\Enums\EventRegistrationState::Full)
                                        <span class="text-xs text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1.5 bg-amber-100 dark:bg-amber-950/50 px-2.5 py-0.5 rounded-full">
                                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                            <span>Kuota Penuh ({{ $event->quota }} Peserta)</span>
                                        </span>
                                    @elseif($state === \App\Enums\EventRegistrationState::DeadlinePassed)
                                        <span class="text-xs text-rose-700 dark:text-rose-400 font-bold flex items-center gap-1.5 bg-rose-100 dark:bg-rose-950/50 px-2.5 py-0.5 rounded-full">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            <span>Batas Pendaftaran Terlewat</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-600 dark:text-slate-400 font-bold flex items-center gap-1.5 bg-slate-200 dark:bg-slate-700 px-2.5 py-0.5 rounded-full">
                                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            <span>Pendaftaran Ditutup</span>
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-700 dark:text-slate-200 leading-snug">
                                    {{ $event->title }}
                                </h3>

                                <div class="grid sm:grid-cols-3 gap-3 pt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>{{ $event->starts_at?->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="clock" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>{{ $event->starts_at?->format('H:i') }} WIB</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 flex-shrink-0"></i>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Box -->
                            <div class="lg:border-l lg:border-gray-200 lg:dark:border-slate-700 lg:pl-8 flex flex-col justify-center gap-3">
                                <!-- Tombol Nonaktif Jelas -->
                                <button type="button" disabled class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold bg-slate-200 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 cursor-not-allowed flex items-center justify-center gap-2 border border-slate-300 dark:border-slate-600">
                                    <i data-lucide="{{ $lucideIcon }}" class="w-4 h-4"></i>
                                    <span>{{ $buttonLabel }}</span>
                                </button>

                                <!-- Tautan Tanya Panitia WhatsApp -->
                                <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn-outline w-full justify-center text-xs py-2 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 flex items-center gap-1.5 transition">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    <span>Tanya panitia via WhatsApp</span>
                                </a>

                                <a href="{{ route('bimtek.show', $event->slug) }}" class="text-center text-xs text-slate-500 hover:text-slate-800 dark:hover:text-white underline font-medium">
                                    Lihat Rincian Kegiatan
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
