@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil – DPD JULEHA Kabupaten Brebes')
@section('meta_description', 'Konfirmasi pendaftaran bimbingan teknis juru sembelih halal DPD JULEHA Brebes.')

@section('content')
<section class="section-padding bg-slate-50 dark:bg-slate-900 min-h-[75vh] flex items-center">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center" data-aos="zoom-in">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 sm:p-12 shadow-xl border border-gray-100 dark:border-slate-700">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i data-lucide="check-check" class="w-10 h-10 stroke-[2.5]"></i>
            </div>

            <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-800 dark:text-white mb-2">
                Pendaftaran Berhasil Diterima!
            </h1>
            <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mb-8">
                Data Anda telah tersimpan di sistem DPD JULEHA Kabupaten Brebes. Silakan simpan kode registrasi Anda sebagai bukti resmi.
            </p>

            @if ($registration)
                <!-- Registration Code Display -->
                <div class="bg-brand-light dark:bg-brand-hero-dark/60 border-2 border-dashed border-brand/40 dark:border-brand-dark/40 rounded-2xl p-6 mb-8 text-center">
                    <span class="text-xs font-bold uppercase tracking-widest text-brand dark:text-brand-dark block mb-1">
                        Kode Registrasi Anda
                    </span>
                    <div class="font-mono font-extrabold text-2xl sm:text-4xl text-brand dark:text-brand-dark tracking-wider">
                        {{ $registration->code }}
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 block">
                        Tunjukkan kode ini saat registrasi ulang atau konfirmasi ke pengurus.
                    </span>
                </div>

                <!-- Registration Summary Table -->
                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-5 mb-8 text-left text-xs sm:text-sm space-y-2.5 border border-gray-100 dark:border-slate-800">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nama Peserta:</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ $registration->full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Kegiatan Bimtek:</span>
                        <span class="font-bold text-slate-800 dark:text-white text-right">{{ $registration->event?->title ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nomor WhatsApp:</span>
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $registration->phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Domisili Kecamatan:</span>
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $registration->district }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Status Awal:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-amber-600 dark:text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $registration->status_label }}
                        </span>
                    </div>
                </div>

                <!-- CTA WhatsApp Confirmation Button -->
                @if ($waUrl)
                    <div class="space-y-3">
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2.5 w-full bg-[#25D366] hover:bg-[#1EBE5D] text-white font-bold py-4 px-6 rounded-2xl shadow-lg transition-transform hover:-translate-y-0.5 text-base">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                            Konfirmasi Sekarang via WhatsApp
                        </a>
                        <p class="text-[11px] text-slate-400">
                            Menghubungi nomor resmi sekretariat: <strong>{{ $adminPhone }}</strong>
                        </p>
                    </div>
                @endif
            @else
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-6 mb-6 text-slate-600 dark:text-slate-300 text-sm">
                    Silakan gunakan tautan dari konfirmasi pendaftaran Anda atau hubungi narahubung DPD JULEHA Brebes.
                </div>
            @endif

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-slate-700 flex flex-wrap justify-center gap-4 text-xs font-semibold">
                <a href="{{ route('home') }}" class="text-slate-500 dark:text-slate-400 hover:text-brand dark:hover:text-brand-dark">
                    &larr; Kembali ke Beranda
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <a href="{{ route('bimtek.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-brand dark:hover:text-brand-dark">
                    Lihat Jadwal Lainnya
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <a href="{{ route('kontak') }}" class="text-slate-500 dark:text-slate-400 hover:text-brand dark:hover:text-brand-dark">
                    Kontak Sekretariat
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
