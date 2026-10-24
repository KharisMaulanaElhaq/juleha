@extends('layouts.portal')

@section('title', 'Konfirmasi Pendaftaran – ' . ($event->title ?? 'Kegiatan JULEHA'))
@section('meta_description', 'Konfirmasi pendaftaran kegiatan bimbingan teknis dan sertifikasi DPD JULEHA Kabupaten Brebes.')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl portal-surface border portal-border portal-text text-xs sm:text-sm font-semibold hover:portal-surface-subtle shadow-xs transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-[var(--primary)]"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    @if(session('error'))
        <div class="p-3.5 rounded-xl portal-badge-danger border portal-border text-xs sm:text-sm flex items-center gap-2.5">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="portal-card overflow-hidden">
        
        <!-- Header Ramping -->
        <div class="p-5 sm:p-6 portal-surface-subtle border-b portal-border space-y-1">
            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider portal-badge-neutral">
                {{ $event->type?->label() ?? 'Kegiatan' }}
            </span>
            <h1 class="text-lg sm:text-xl font-bold portal-text leading-snug">
                Konfirmasi Pendaftaran Pelatihan
            </h1>
            <p class="text-xs portal-text-muted">
                Pastikan data identitas Anda sudah sesuai sebelum mengirimkan formulir pendaftaran.
            </p>
        </div>

        <div class="p-5 sm:p-6 space-y-5">
            
            <!-- Rincian Kegiatan -->
            <div class="portal-surface-subtle p-4 rounded-xl border portal-border space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider portal-text-muted block">
                    Kegiatan yang Dipilih:
                </span>
                <div class="font-bold text-base portal-text">
                    {{ $event->title }}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs portal-text-muted pt-2 border-t portal-border">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 portal-text-muted"></i>
                        <span>{{ $event->starts_at?->translatedFormat('l, d F Y - H:i') }} WIB</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 portal-text-muted"></i>
                        <span>{{ $event->location }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 portal-text-muted"></i>
                        <span>Sisa Kuota: <strong class="portal-text">{{ $event->remaining_quota }} peserta</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="banknote" class="w-3.5 h-3.5 portal-text-muted"></i>
                        <span>Biaya: <strong class="portal-text">{{ $event->fee > 0 ? 'Rp ' . number_format($event->fee, 0, ',', '.') : 'Gratis / Subsidi' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Rincian Peserta -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold portal-text uppercase tracking-wider">
                        Data Identitas Peserta (Sesuai KTP):
                    </span>
                    <a href="{{ route('participant.profile') }}" class="text-xs font-semibold text-[var(--primary)] hover:underline">
                        Ubah Data &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3.5 rounded-xl portal-surface-subtle border portal-border text-xs">
                    <div>
                        <span class="portal-text-muted">Nama Lengkap (KTP):</span>
                        <div class="font-bold portal-text mt-0.5">{{ $profile->full_name_ktp }}</div>
                    </div>
                    <div>
                        <span class="portal-text-muted">NIK (Tersamar):</span>
                        <div class="font-mono font-bold portal-text mt-0.5">{{ $profile->masked_nik }}</div>
                    </div>
                    <div>
                        <span class="portal-text-muted">Nomor WhatsApp:</span>
                        <div class="font-semibold portal-text mt-0.5">{{ $profile->formatted_phone }}</div>
                    </div>
                    <div>
                        <span class="portal-text-muted">Domisili:</span>
                        <div class="font-semibold portal-text mt-0.5">Kec. {{ $profile->district }}, Desa {{ $profile->village }}</div>
                    </div>
                </div>
            </div>

            <!-- Form Submit -->
            <form action="{{ route('enrollment.store', $event->slug) }}" method="POST" class="pt-3 border-t portal-border space-y-3">
                @csrf
                <p class="text-[11px] portal-text-muted leading-relaxed">
                    Dengan mendaftar, Anda menyetujui ketentuan pada 
                    <a href="{{ route('syarat-ketentuan') }}" target="_blank" class="text-[var(--primary)] font-semibold underline">Syarat &amp; Ketentuan</a> serta 
                    <a href="{{ route('kebijakan-privasi') }}" target="_blank" class="text-[var(--primary)] font-semibold underline">Kebijakan Privasi</a>. 
                    Status pendaftaran Anda akan dicatat sebagai <strong>Menunggu Konfirmasi</strong> dan akan ditinjau oleh panitia DPD JULEHA Brebes.
                </p>

                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold portal-text-muted hover:portal-text hover:portal-surface-subtle transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="h-11 px-5 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-purple-400">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Kirim Pendaftaran Sekarang</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
