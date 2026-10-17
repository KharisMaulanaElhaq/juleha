@extends('layouts.portal')

@section('title', ($status === 'success' ? 'Absen Berhasil' : 'Pemberitahuan Absen') . ' – ' . setting('site_name', 'DPD JULEHA Brebes'))
@section('meta_description', 'Hasil verifikasi absen kegiatan DPD JULEHA Kabupaten Brebes.')

@section('content')
<div class="max-w-md mx-auto py-4 sm:py-8 space-y-4">

    @php
        $waRaw = setting('whatsapp_number', '6282324448886');
        $waAdmin = function_exists('normalize_wa_number') ? normalize_wa_number($waRaw) : preg_replace('/\D/', '', $waRaw);
        $waHelpText = urlencode('Halo Panitia DPD JULEHA Brebes, saya ' . (auth()->user()?->name ?? 'Peserta') . '. Saya mengalami kendala saat absen: ' . $message);
    @endphp

    @if($status === 'success')
        <!-- ==================== LAYAR BERHASIL ==================== -->
        <div class="portal-card p-6 sm:p-8 text-center space-y-5">
            
            <div class="w-16 h-16 mx-auto rounded-2xl portal-badge-success flex items-center justify-center shadow-xs">
                <i data-lucide="check" class="w-9 h-9 stroke-[2.5]"></i>
            </div>

            <div class="space-y-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider portal-badge-success">
                    Kehadiran Tercatat
                </span>
                <h1 class="text-xl sm:text-2xl font-bold portal-text">
                    Absen Berhasil!
                </h1>
                <p class="text-sm font-semibold text-[var(--success-text)]">
                    {{ $message }}
                </p>
            </div>

            <!-- Detail Ringkas -->
            <div class="portal-surface-subtle rounded-xl p-4 text-left text-xs sm:text-sm space-y-2 border portal-border">
                <div class="flex justify-between py-1 border-b portal-border">
                    <span class="portal-text-muted">Nama:</span>
                    <span class="font-semibold portal-text">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
                </div>
                <div class="flex justify-between py-1 border-b portal-border">
                    <span class="portal-text-muted">Sesi:</span>
                    <span class="font-semibold portal-text">{{ $session->title }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="portal-text-muted">Waktu Absen:</span>
                    <span class="font-mono font-semibold text-[var(--success-text)]">
                        {{ $attendance?->attended_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') ?? now('Asia/Jakarta')->format('H.i') }} WIB
                    </span>
                </div>
            </div>

            <div class="pt-1">
                <a href="{{ route('dashboard') }}"
                   class="w-full h-11 inline-flex items-center justify-center gap-2 px-5 rounded-xl portal-btn-primary font-semibold text-sm shadow-xs transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>

    @elseif($status === 'already')
        <!-- ==================== SUDAH PERNAH ABSEN ==================== -->
        <div class="portal-card p-6 sm:p-8 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl portal-icon-box flex items-center justify-center shadow-xs">
                <i data-lucide="calendar-check" class="w-8 h-8 stroke-[2.5]"></i>
            </div>

            <div class="space-y-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider portal-badge-neutral">
                    Sudah Tercatat
                </span>
                <h1 class="text-xl sm:text-2xl font-bold portal-text">
                    Anda Sudah Absen
                </h1>
                <p class="text-xs sm:text-sm portal-text-muted">
                    {{ $message }}
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('dashboard') }}"
                   class="w-full h-11 inline-flex items-center justify-center gap-2 px-5 rounded-xl portal-btn-primary font-semibold text-sm shadow-xs transition">
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>

    @else
        <!-- ==================== GAGAL / PERLU BANTUAN ==================== -->
        <div class="portal-card p-6 sm:p-8 text-center space-y-4">
            
            <div class="w-14 h-14 mx-auto rounded-2xl portal-badge-danger flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-8 h-8 stroke-[2.2]"></i>
            </div>

            <div class="space-y-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider portal-badge-danger">
                    Pemberitahuan
                </span>
                <h1 class="text-lg sm:text-xl font-bold portal-text">
                    {{ $title }}
                </h1>
                <p class="text-xs sm:text-sm portal-badge-danger p-3 rounded-xl border portal-border">
                    {{ $message }}
                </p>
            </div>

            <!-- Petunjuk Langkah Selanjutnya -->
            <div class="portal-surface-subtle rounded-xl p-3.5 text-left text-xs portal-text-muted space-y-1 border portal-border">
                <strong class="portal-text block font-semibold">Langkah Selanjutnya:</strong>
                <p class="leading-relaxed">
                    @if($code === 'token_expired' || $code === 'code_invalid')
                        Kode QR atau angka telah berganti. Silakan arahkan kamera kembali ke layar proyektor atau masukkan kode angka 6 digit terbaru.
                    @elseif($code === 'session_closed')
                        Absen belum dibuka oleh instruktur. Silakan tunggu aba-aba panitia.
                    @elseif($code === 'not_enrolled')
                        Anda belum terdaftar pada kegiatan ini. Silakan hubungi panitia untuk dibantu mendaftar.
                    @elseif($code === 'status_pending')
                        Pendaftaran Anda masih menunggu verifikasi panitia.
                    @else
                        Tunjukkan nama Anda ke panitia di meja depan untuk dibantu absen manual.
                    @endif
                </p>
            </div>

            <!-- Tombol Aksi & WhatsApp -->
            <div class="space-y-2 pt-2">
                @if($code === 'token_expired' || $code === 'code_invalid')
                    <a href="{{ route('attendance.absen') }}"
                       class="w-full h-11 inline-flex items-center justify-center gap-2 px-4 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Coba Absen Lagi</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}"
                       class="w-full h-11 inline-flex items-center justify-center gap-2 px-4 rounded-xl portal-btn-subtle font-semibold text-xs sm:text-sm transition">
                        <span>Kembali ke Beranda</span>
                    </a>
                @endif

                <!-- Tautan WhatsApp Panitia -->
                <a href="https://wa.me/{{ $waAdmin }}?text={{ $waHelpText }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="w-full h-11 inline-flex items-center justify-center gap-2 px-4 rounded-xl portal-btn-whatsapp font-semibold text-xs sm:text-sm shadow-xs transition">
                    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.174.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                    </svg>
                    <span>Hubungi Panitia via WhatsApp</span>
                </a>
            </div>
        </div>

    @endif

</div>
@endsection
