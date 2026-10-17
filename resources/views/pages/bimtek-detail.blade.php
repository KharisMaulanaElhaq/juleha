@extends('layouts.app')

@section('title', ($event->title ?? 'Rincian Bimbingan Teknis') . ' – DPD JULEHA Brebes')
@section('meta_description', Str::limit($event->description ?? 'Pendaftaran bimbingan teknis juru sembelih halal DPD JULEHA Kabupaten Brebes.', 160))

@section('content')
<!-- ===================== HERO DETAIL BIMTEK ===================== -->
<section class="relative py-20" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-flex items-center gap-1.5 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">
                    {{ $event->category ?? 'Bimbingan Teknis' }}
                </span>
                <span class="text-xs font-bold {{ $event->isOpen() ? 'text-brand-highlight' : 'text-amber-300' }}">
                    {{ $event->status === 'open' ? 'Pendaftaran Dibuka' : ($event->status === 'closed' ? 'Pendaftaran Ditutup' : 'Kegiatan Selesai') }}
                </span>
            </div>

            <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-5xl text-white leading-tight mb-4">
                {{ $event->title }}
            </h1>

            <p class="text-slate-200 text-base sm:text-lg leading-relaxed mb-6">
                {{ $event->description }}
            </p>

            <div class="flex flex-wrap gap-4 text-xs sm:text-sm text-brand-highlight">
                <span class="flex items-center gap-2 bg-white/10 px-3.5 py-1.5 rounded-lg">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    {{ $event->starts_at ? $event->starts_at->translatedFormat('l, d F Y') : '-' }}
                </span>
                <span class="flex items-center gap-2 bg-white/10 px-3.5 py-1.5 rounded-lg">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    {{ $event->starts_at ? $event->starts_at->format('H:i') : '08:00' }} - {{ $event->ends_at ? $event->ends_at->format('H:i') : '16:00' }} WIB
                </span>
                <span class="flex items-center gap-2 bg-white/10 px-3.5 py-1.5 rounded-lg">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    {{ $event->location }}
                </span>
                <span class="flex items-center gap-2 bg-white/10 px-3.5 py-1.5 rounded-lg">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Kuota: {{ $event->quota }} Peserta
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ===================== DETAIL CONTENT & FORM ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Flash Alert jika dialihkan dari form pendaftaran yang tertutup/penuh -->
        @if(session('error'))
            <div class="mb-8 p-4 sm:p-5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-start gap-3 shadow-soft" role="alert">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <strong class="font-bold block mb-0.5">Pemberitahuan Pendaftaran</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @elseif(session('info'))
            <div class="mb-8 p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 flex items-start gap-3 shadow-soft" role="alert">
                <i data-lucide="info" class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <strong class="font-bold block mb-0.5">Informasi</strong>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-12">
            <!-- Left Info Columns -->
            <div class="lg:col-span-2 space-y-10">
                <!-- Materi Pelatihan -->
                <div>
                    <h3 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white mb-5 flex items-center gap-2">
                        <i data-lucide="book-open" class="w-6 h-6 text-brand dark:text-brand-dark"></i>
                        Kurikulum &amp; Materi Pelatihan
                    </h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @forelse ($event->curriculum ?? [] as $m)
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-brand dark:text-brand-dark mt-0.5 flex-shrink-0"></i>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $m }}</span>
                        </div>
                        @empty
                        <div class="col-span-2 text-sm text-slate-500 dark:text-slate-400">
                            Materi meliputi Fiqih Sembelih Syar'i, Teknik Handling & Perebahan, serta Asah & Uji Ketajaman Bilah.
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Fasilitas -->
                <div>
                    <h3 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white mb-5 flex items-center gap-2">
                        <i data-lucide="package-check" class="w-6 h-6 text-brand-accent dark:text-brand-dark-accent"></i>
                        Fasilitas Peserta
                    </h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @forelse ($event->facilities ?? [] as $f)
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 flex items-start gap-3">
                            <i data-lucide="sparkles" class="w-5 h-5 text-brand-accent dark:text-brand-dark-accent mt-0.5 flex-shrink-0"></i>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $f }}</span>
                        </div>
                        @empty
                        <div class="col-span-2 text-sm text-slate-500 dark:text-slate-400">
                            Sertifikat Resmi DPD JULEHA Brebes, Modul Pelatihan, Konsumsi, dan Akses Jejaring Alumni Juleha.
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Persyaratan -->
                <div>
                    <h3 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white mb-5 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-6 h-6 text-brand dark:text-brand-dark"></i>
                        Persyaratan Peserta
                    </h3>
                    <ul class="space-y-2.5 text-sm text-slate-600 dark:text-slate-300">
                        @forelse ($event->requirements ?? [] as $p)
                        <li class="flex items-start gap-3">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-brand dark:text-brand-dark mt-1 flex-shrink-0"></i>
                            <span>{{ $p }}</span>
                        </li>
                        @empty
                        <li class="flex items-start gap-3">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-brand dark:text-brand-dark mt-1 flex-shrink-0"></i>
                            <span>Muslim baligh dan berakal sehat.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-brand dark:text-brand-dark mt-1 flex-shrink-0"></i>
                            <span>Berkomitmen mengikuti seluruh sesi pembekalan materi dan praktik.</span>
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Right Registration Action Card -->
            <div>
                @php
                    $state = $event->registrationState();
                    $seatsLeft = $event->seatsLeft();
                    $waLink = setting_wa_link('Halo Panitia DPD JULEHA Brebes, saya ingin bertanya tentang pendaftaran ' . $event->title);
                @endphp
                <div class="sticky top-28 bg-gradient-to-br from-brand-hero-dark to-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-2xl border border-brand/30">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        @if ($state === \App\Enums\EventRegistrationState::Open)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Pendaftaran Dibuka</span>
                            </span>
                        @elseif ($state === \App\Enums\EventRegistrationState::Full)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span>Kuota Penuh</span>
                            </span>
                        @elseif ($state === \App\Enums\EventRegistrationState::DeadlinePassed)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-400/30">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                <span>Batas Lewat</span>
                            </span>
                        @elseif ($state === \App\Enums\EventRegistrationState::Finished)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-500/20 text-slate-300 border border-slate-400/30">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                <span>Kegiatan Selesai</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-red-500/20 text-red-300 border border-red-400/30">
                                <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                <span>Pendaftaran Tutup</span>
                            </span>
                        @endif

                        <span class="text-xs text-slate-300">
                            @if(is_null($seatsLeft))
                                Kuota: <strong class="text-white">Terbuka</strong>
                            @else
                                Sisa: <strong class="text-white">{{ $seatsLeft }}</strong> / {{ $event->quota }}
                            @endif
                        </span>
                    </div>

                    <h3 class="font-heading font-bold text-xl mb-2">Pendaftaran Bimtek Online</h3>
                    <p class="text-xs text-slate-300 mb-6 leading-relaxed">
                        @if($event->registration_deadline)
                            Batas pendaftaran: <strong class="text-white">{{ $event->registration_deadline->translatedFormat('d M Y H:i') }} WIB</strong>.
                        @else
                            Segera amankan kursi Anda sebelum kuota angkatan ini terpenuhi.
                        @endif
                    </p>

                    <div class="space-y-4 mb-6 text-xs text-slate-300 bg-white/5 p-4 rounded-xl border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Biaya / Kontribusi:</span>
                            <span class="font-bold text-brand-dark-accent">
                                {{ $event->fee > 0 ? 'Rp ' . number_format($event->fee, 0, ',', '.') : 'Gratis / Infaq' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Total Kuota:</span>
                            <span class="font-semibold text-white">
                                {{ is_null($event->quota) ? 'Tanpa Batas Kuota' : $event->quota . ' Peserta' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Ketersediaan Kursi:</span>
                            <span class="font-semibold">
                                @if(is_null($seatsLeft))
                                    <span class="text-brand-dark-accent">Terbuka</span>
                                @elseif($seatsLeft <= 5 && $seatsLeft > 0)
                                    <span class="text-amber-400 font-bold animate-pulse">Tinggal {{ $seatsLeft }} kursi!</span>
                                @elseif($seatsLeft > 0)
                                    <span class="text-brand-dark-accent">{{ $seatsLeft }} kursi tersisa</span>
                                @else
                                    <span class="text-rose-400 font-bold">Habis / Penuh</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    @if ($event->canRegister())
                        <a href="{{ route('bimtek.daftar', $event->slug) }}" class="btn-primary w-full justify-center text-sm py-3.5 text-center flex items-center gap-2 shadow-lg">
                            <span>Isi Formulir Pendaftaran</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <!-- Kotak Penjelasan Alasan Ramah -->
                        <div class="p-3.5 bg-white/10 border border-white/20 rounded-xl text-center text-xs text-slate-200 mb-3 leading-relaxed">
                            {{ $event->registrationStateMessage() }}
                        </div>

                        <!-- Tombol Nonaktif Jelas -->
                        <button type="button" disabled class="w-full py-3 px-4 rounded-xl text-xs font-semibold bg-white/10 text-slate-400 cursor-not-allowed flex items-center justify-center gap-2 border border-white/15 mb-2.5">
                            <i data-lucide="{{ $state->lucideIcon() }}" class="w-4 h-4"></i>
                            <span>{{ $state->buttonLabel() }}</span>
                        </button>

                        <!-- Tautan WhatsApp Bantuan -->
                        <a href="{{ $waLink }}"
                            target="_blank" rel="noopener noreferrer"
                            class="btn-accent w-full justify-center text-xs py-2.5 text-center flex items-center gap-2 mb-2">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Tanya Panitia via WhatsApp</span>
                        </a>

                        <a href="{{ route('bimtek.index') }}" class="btn-outline w-full justify-center text-xs py-2 text-center text-white border-white/20">
                            Lihat Jadwal Bimtek Lainnya
                        </a>
                    @endif

                    <div class="mt-6 pt-4 border-t border-white/10 text-center text-xs text-slate-400">
                        Pertanyaan atau bantuan? Hubungi WhatsApp resmi di
                        <a href="{{ setting_wa_link() }}" target="_blank" rel="noopener noreferrer" class="text-brand-highlight underline font-semibold">
                            {{ setting_wa_display('whatsapp_number') }}
                        </a>
                    </div>
                </div>
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
