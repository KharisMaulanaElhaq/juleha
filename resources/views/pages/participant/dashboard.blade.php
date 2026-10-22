@extends('layouts.portal')

@section('title', 'Beranda Peserta – ' . setting('site_name', 'DPD JULEHA Brebes'))
@section('meta_description', 'Portal resmi peserta pelatihan dan bimbingan teknis DPD JULEHA Kabupaten Brebes.')

@section('content')
@php
    $displayName = $user->first_name ?? 'Peserta';
    $fullName = $profile?->full_name_ktp ?? $user->name;
    $waRaw = setting('whatsapp_number', '6282324448886');
    $waNumber = function_exists('normalize_wa_number') ? normalize_wa_number($waRaw) : preg_replace('/\D/', '', $waRaw);
    $waHelpUrl = 'https://wa.me/' . $waNumber . '?text=' . urlencode('Halo Panitia DPD JULEHA Brebes, saya ' . $fullName . '. Saya butuh bantuan terkait kegiatan pelatihan.');
@endphp

<div class="space-y-6">

    <!-- ========================================================= -->
    <!-- 1. SAPAAN SATU BARIS (ELEGAN & SUMBER NAMA KONSISTEN)     -->
    <!-- ========================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold portal-text tracking-tight">
                Selamat datang, {{ $displayName }}
            </h1>
            <span class="sr-only">{{ $fullName }}</span>
            <p class="text-xs sm:text-sm portal-text-muted mt-0.5">
                Pantau kegiatan dan absen Anda di sini.
            </p>
        </div>
        <div class="text-xs portal-text-muted flex items-center gap-1.5 self-start sm:self-auto portal-surface px-3 py-1.5 rounded-xl border portal-border shadow-xs">
            <i data-lucide="calendar" class="w-3.5 h-3.5 portal-text-muted"></i>
            <span>{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 2. GRID DUA KOLOM DESKTOP: AKSI UTAMA & UBIN MENU         -->
    <!-- ========================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ==================== KOLOM KIRI: KARTU AKSI UTAMA & PERINGATAN DATA ==================== -->
        <div class="lg:col-span-7 space-y-4">
            
            @if($primarySession && $primarySessionAttended)
                <!-- KONDISI D: SUDAH ABSEN (KARTU HIJAU LEMBUT) -->
                <div class="portal-card p-5 sm:p-6 border-t-2 border-[var(--gold)] flex flex-col justify-between space-y-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl portal-badge-success flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-5 h-5 stroke-[2.5]"></i>
                        </div>
                        <div class="space-y-1">
                            <span class="inline-block text-[11px] font-bold uppercase tracking-wider portal-badge-success px-2.5 py-0.5 rounded-full">
                                Kehadiran Tercatat
                            </span>
                            <h2 class="text-lg sm:text-xl font-bold portal-text">
                                Anda sudah absen pukul {{ $primarySessionAttendedTime }}
                            </h2>
                            <p class="text-xs sm:text-sm portal-text-muted">
                                Sesi: <strong class="portal-text">{{ $primarySession->title }}</strong> &bull; {{ $primarySession->event?->title }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t portal-border flex items-center justify-between text-xs portal-text-muted">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span>Waktu sesi: {{ $primarySession->formatted_schedule }} WIB</span>
                        </span>
                        <a href="{{ route('participant.profile') }}#riwayat" class="font-semibold underline hover:portal-text">
                            Lihat Riwayat &rarr;
                        </a>
                    </div>
                </div>

            @elseif($primarySession && $primarySession->isOpenForAttendance())
                <!-- KONDISI A: ADA SESI HARI INI & ABSEN DIBUKA -->
                <div class="portal-card p-5 sm:p-6 border-t-2 border-[var(--gold)] flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider portal-badge-success">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Sesi Berlangsung
                            </span>
                            <span class="text-xs font-semibold portal-text-muted flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <span>{{ $primarySession->formatted_schedule }} WIB</span>
                            </span>
                        </div>

                        <div>
                            <h2 class="text-lg sm:text-xl font-bold portal-text leading-snug">
                                {{ $primarySession->title }}
                            </h2>
                            <p class="text-xs sm:text-sm portal-text-muted mt-0.5">
                                {{ $primarySession->event?->title }}
                            </p>
                        </div>

                        @if($primarySession->location)
                            <div class="flex items-center gap-1.5 text-xs portal-text-muted">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                <span>{{ $primarySession->location }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Tombol Utama: Absen sekarang -->
                    <div class="space-y-2 pt-1">
                        <a href="{{ route('attendance.absen') }}"
                           class="w-full h-12 inline-flex items-center justify-center gap-2 px-5 rounded-xl portal-btn-primary font-bold text-base shadow-xs focus:outline-none focus:ring-2 focus:ring-purple-400">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                            <span>Absen sekarang</span>
                            <span class="sr-only">ABSEN SEKARANG</span>
                        </a>

                        <div class="text-center">
                            <a href="{{ route('attendance.absen', ['tab' => 'kode']) }}"
                               class="text-xs sm:text-sm portal-text-muted hover:portal-text underline transition">
                                Kamera bermasalah? Masukkan kode angka
                                <span class="sr-only">Kamera tidak bisa? Isi kode angka</span>
                            </a>
                        </div>
                    </div>
                </div>

            @elseif($primarySession && ! $primarySession->isOpenForAttendance())
                <!-- KONDISI B: SESI HARI INI TAPI ABSEN BELUM DIBUKA -->
                <div class="portal-card p-5 sm:p-6 border-t-2 border-[var(--gold)] flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="inline-block text-[11px] font-bold uppercase tracking-wider portal-badge-warning px-2.5 py-0.5 rounded-full">
                                Menunggu Dimulai
                            </span>
                            <span class="text-xs font-semibold portal-text-muted flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <span>{{ $primarySession->formatted_schedule }} WIB</span>
                            </span>
                        </div>

                        <div>
                            <h2 class="text-lg sm:text-xl font-bold portal-text leading-snug">
                                {{ $primarySession->title }}
                            </h2>
                            <p class="text-xs sm:text-sm portal-text-muted mt-0.5">
                                {{ $primarySession->event?->title }}
                            </p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl portal-surface-subtle border portal-border text-xs sm:text-sm portal-text space-y-0.5">
                        <p class="font-medium">
                            Absen belum dibuka. Tunggu arahan panitia.
                        </p>
                        <span class="sr-only">Presensi belum dibuka, tunggu arahan panitia.</span>
                        <p class="text-[11px] portal-text-muted">
                            Tombol absen akan otomatis aktif saat sesi pelatihan dimulai.
                        </p>
                    </div>
                </div>

            @else
                <!-- KONDISI C: TIDAK ADA SESI HARI INI -->
                <div class="portal-card p-5 sm:p-6 border-t-2 border-[var(--gold)] flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg portal-icon-box flex items-center justify-center shrink-0">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold portal-text leading-tight">
                                    Tidak ada kegiatan hari ini
                                </h2>
                                <span class="sr-only">Belum ada kegiatan hari ini</span>
                                <p class="text-xs portal-text-muted">
                                    Tidak ada jadwal pelatihan yang berlangsung hari ini.
                                </p>
                            </div>
                        </div>

                        @if($upcomingActiveEnrollment && $upcomingActiveEnrollment->event)
                            @php $ev = $upcomingActiveEnrollment->event; @endphp
                            <div class="p-3.5 rounded-xl portal-surface-subtle border portal-border space-y-1">
                                <span class="text-[11px] font-bold portal-text-muted uppercase tracking-wider block">
                                    Jadwal Kegiatan Berikutnya:
                                </span>
                                <div class="text-xs sm:text-sm font-bold portal-text">
                                    {{ $ev->title }}
                                </div>
                                <div class="text-[11px] sm:text-xs portal-text-muted flex flex-wrap gap-x-3 gap-y-1 pt-1">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3 portal-text-muted"></i>
                                        <span>{{ $ev->starts_at?->translatedFormat('l, d F Y') }}</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3 portal-text-muted"></i>
                                        <span>{{ $ev->starts_at?->format('H.i') }} WIB</span>
                                    </span>
                                    @if($ev->location)
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3 h-3 portal-text-muted"></i>
                                            <span>{{ $ev->location }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-1">
                        <a href="#kegiatan-dibuka"
                           class="w-full h-10 inline-flex items-center justify-center gap-1.5 px-4 rounded-xl portal-btn-subtle font-semibold text-xs sm:text-sm">
                            <span>Lihat kegiatan yang dibuka</span>
                            <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

            @endif

            <!-- ==================== BARIS KELENGKAPAN DATA (DI BAWAH KARTU AKSI UTAMA) ==================== -->
            @if(! empty($missingProfileFields))
                @php
                    $countMissing = count($missingProfileFields);
                    $shortMap = [
                        'Nama Lengkap Sesuai KTP' => 'Nama KTP',
                        'Nomor Induk Kependudukan (NIK)' => 'NIK',
                        'Nomor WhatsApp' => 'WhatsApp',
                        'Kecamatan / Domisili' => 'Kecamatan',
                    ];
                    $shortList = array_map(fn($f) => $shortMap[$f] ?? $f, $missingProfileFields);
                    $fieldsText = implode(', ', $shortList);
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 px-3.5 py-2.5 rounded-xl portal-badge-warning border portal-border text-xs sm:text-sm shadow-xs">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>Data Anda <strong>tinggal {{ $countMissing }} lagi</strong>: {{ $fieldsText }}</span>
                    </div>
                    <a href="{{ route('participant.profile') }}"
                       class="inline-flex items-center justify-center gap-1 h-7 px-2.5 rounded-lg portal-btn-primary font-semibold text-xs transition self-start sm:self-auto shrink-0 shadow-xs">
                        <span>Lengkapi</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            @endif

            @if($profile && ! $profile->hasCurrentConsent())
                <!-- Baris Tipis: Perbarui Persetujuan Data -->
                <div class="flex items-center justify-between gap-3 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800/60 text-xs shadow-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400"></i>
                        <span class="truncate">Persetujuan data sertifikasi perlu diperbarui sebelum daftar uji kompetensi.</span>
                    </div>
                    <a href="{{ route('participant.profile', ['step' => 2]) }}#persetujuan-data"
                       class="inline-flex items-center justify-center gap-1 font-semibold text-amber-900 dark:text-amber-100 underline hover:no-underline shrink-0">
                        <span>Perbarui persetujuan data</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            @endif

        </div>

        <!-- ==================== KOLOM KANAN: UBIN MENU (SEPAKET IKON & TEKS KONSISTEN) ==================== -->
        <div class="lg:col-span-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-2 gap-3 items-stretch">
            
            <!-- Ubin 1: Kegiatan Saya -->
            <a href="#kegiatan-saya"
               class="portal-card p-4 hover:border-purple-300 dark:hover:border-purple-700 transition flex flex-col justify-between group">
                <div class="w-10 h-10 rounded-xl portal-icon-box flex items-center justify-center transition-transform group-hover:scale-105">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
                <div class="mt-3">
                    <div class="font-bold text-sm portal-text group-hover:text-[var(--primary)] transition">
                        Kegiatan Saya
                    </div>
                    <div class="text-[11px] portal-text-muted mt-0.5">
                        {{ $myEnrollments->count() }} pelatihan terdaftar
                    </div>
                </div>
            </a>

            <!-- Ubin 2: Materi -->
            <a href="#materi"
               class="portal-card p-4 hover:border-purple-300 dark:hover:border-purple-700 transition flex flex-col justify-between group">
                <div class="w-10 h-10 rounded-xl portal-icon-box flex items-center justify-center transition-transform group-hover:scale-105">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <div class="mt-3">
                    <div class="font-bold text-sm portal-text group-hover:text-[var(--primary)] transition">
                        Materi
                    </div>
                    <div class="text-[11px] portal-text-muted mt-0.5">
                        Modul & bahan ajar
                    </div>
                </div>
            </a>

            <!-- Ubin 3: Sertifikat -->
            <a href="{{ route('participant.certificates') }}"
               class="portal-card p-4 hover:border-purple-300 dark:hover:border-purple-700 transition flex flex-col justify-between group">
                <div class="w-10 h-10 rounded-xl portal-icon-box flex items-center justify-center transition-transform group-hover:scale-105">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
                <div class="mt-3">
                    <div class="font-bold text-sm portal-text group-hover:text-[var(--primary)] transition">
                        Sertifikat
                    </div>
                    <div class="text-[11px] portal-text-muted mt-0.5">
                        Hasil & berkas digital
                    </div>
                </div>
            </a>

            <!-- Ubin 3: Riwayat Absen (2 + 1 melebar penuh di laptop & mobile, tidak ada lubang kosong) -->
            <a href="{{ route('participant.profile') }}#riwayat"
               class="portal-card p-4 hover:border-purple-300 dark:hover:border-purple-700 transition flex flex-row items-center justify-between sm:flex-col sm:items-start sm:justify-between lg:flex-row lg:items-center group col-span-2 md:col-span-1 lg:col-span-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl portal-icon-box flex items-center justify-center transition-transform group-hover:scale-105 shrink-0">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm portal-text group-hover:text-[var(--primary)] transition">
                            Riwayat Absen
                        </div>
                        <div class="text-[11px] portal-text-muted mt-0.5">
                            {{ $myAttendances->count() }} kali hadir
                        </div>
                    </div>
                </div>
                <div class="text-xs font-semibold portal-text-muted group-hover:text-[var(--primary)] transition flex items-center gap-1">
                    <span>Lihat</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            <!-- Ubin 4: Bantuan WhatsApp (Hanya tampil di HP: md:hidden agar tidak duplikasi di desktop) -->
            <a href="{{ $waHelpUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="md:hidden portal-card p-4 hover:border-purple-300 transition flex flex-col justify-between group">
                <div class="w-10 h-10 rounded-xl portal-icon-box flex items-center justify-center transition-transform group-hover:scale-105">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </div>
                <div class="mt-3">
                    <div class="font-bold text-sm portal-text group-hover:text-[var(--primary)] transition">
                        Bantuan
                    </div>
                    <div class="text-[11px] portal-text-muted mt-0.5">
                        Chat WhatsApp panitia
                    </div>
                </div>
            </a>

        </div>

    </div>

    <!-- ========================================================= -->
    <!-- 3. DAFTAR KEGIATAN SAYA                                   -->
    <!-- ========================================================= -->
    <section id="kegiatan-saya" aria-labelledby="heading-kegiatan-saya" class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <h2 id="heading-kegiatan-saya" class="text-base sm:text-lg font-bold portal-text flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-4 h-4 portal-text-muted"></i>
                <span>Kegiatan Saya</span>
            </h2>
            <span class="text-xs portal-text-muted">
                {{ $myEnrollments->count() }} terdaftar
            </span>
        </div>

        @if($myEnrollments->isEmpty())
            <div class="portal-card p-6 text-center border-dashed space-y-2">
                <p class="text-xs sm:text-sm portal-text-muted">
                    Anda belum mendaftar kegiatan bimtek atau sertifikasi apapun.
                </p>
                <a href="#kegiatan-dibuka" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl portal-btn-primary font-semibold text-xs shadow-xs">
                    <span>Lihat kegiatan yang dibuka</span>
                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($myEnrollments as $en)
                    @php
                        $ev = $en->event;
                        $stats = $attendanceStats[$en->event_id] ?? ['total_sessions' => 0, 'hadir_count' => 0];
                    @endphp
                    <div class="portal-card p-4 sm:p-5 space-y-3">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($ev?->type)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider portal-badge-neutral">
                                            {{ $ev->type->label() }}
                                        </span>
                                    @endif

                                    <!-- Status Pendaftaran dengan Warna Semantik Lembut & Ikon -->
                                    @if($en->status === \App\Enums\EnrollmentStatus::Aktif)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-success">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            <span>Diterima</span>
                                        </span>
                                    @elseif($en->status === \App\Enums\EnrollmentStatus::Menunggu)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-warning">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            <span>Menunggu diperiksa</span>
                                        </span>
                                    @elseif($en->status === \App\Enums\EnrollmentStatus::Ditolak)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-danger">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                            <span>Tidak diterima</span>
                                        </span>
                                    @elseif($en->status === \App\Enums\EnrollmentStatus::Selesai)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-neutral">
                                            <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                            <span>Selesai</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-neutral">
                                            <span>{{ $en->status->label() }}</span>
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-bold text-sm sm:text-base portal-text">
                                    {{ $ev?->title ?? 'Kegiatan JULEHA' }}
                                </h3>

                                <div class="text-xs portal-text-muted flex flex-wrap gap-x-3 gap-y-1">
                                    @if($ev?->starts_at)
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                            <span>{{ $ev->starts_at->translatedFormat('d F Y') }}</span>
                                        </span>
                                    @endif
                                    @if($ev?->location)
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                                            <span>{{ $ev->location }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Aksi & Status Kehadiran Sederhana -->
                            <div class="flex flex-col sm:items-end gap-1.5 shrink-0 pt-1 sm:pt-0">
                                <span class="text-xs font-medium portal-text-muted portal-surface-subtle px-2.5 py-1 rounded-lg border portal-border">
                                    Sudah hadir {{ $stats['hadir_count'] }} dari {{ $stats['total_sessions'] }} pertemuan
                                </span>

                                @if($en->status === \App\Enums\EnrollmentStatus::Aktif || $en->status === \App\Enums\EnrollmentStatus::Selesai)
                                    @if($ev)
                                        <a href="{{ route('participant.event.materials', $ev->slug) }}"
                                           class="inline-flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg portal-btn-primary font-semibold text-xs shadow-xs">
                                            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                            <span>Buka Materi</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Catatan Alasan Bila Ditolak -->
                        @if($en->status === \App\Enums\EnrollmentStatus::Ditolak && $en->admin_note)
                            <div class="p-2.5 rounded-xl portal-badge-danger text-xs space-y-0.5">
                                <strong>Catatan Panitia:</strong> {{ $en->admin_note }}
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- ========================================================= -->
    <!-- 4. BAGIAN MATERI KEGIATAN YANG DIIKUTI                     -->
    <!-- ========================================================= -->
    <section id="materi" aria-labelledby="heading-materi" class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <h2 id="heading-materi" class="text-base sm:text-lg font-bold portal-text flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 portal-text-muted"></i>
                <span>Materi Pelatihan</span>
            </h2>
        </div>

        @php
            $activeEnrollments = $myEnrollments->whereIn('status', [\App\Enums\EnrollmentStatus::Aktif, \App\Enums\EnrollmentStatus::Selesai]);
        @endphp

        @if($activeEnrollments->isEmpty())
            <div class="portal-card p-5 text-center text-xs sm:text-sm portal-text-muted">
                Materi pembelajaran otomatis terbuka setelah pendaftaran pelatihan Anda disetujui oleh panitia.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($activeEnrollments as $actEn)
                    @if($actEn->event)
                        <div class="portal-card p-4 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider portal-badge-neutral px-2 py-0.5 rounded-full inline-block">
                                    Kelas Terbuka
                                </span>
                                <h3 class="font-bold text-sm portal-text mt-1.5">
                                    {{ $actEn->event->title }}
                                </h3>
                                <p class="text-[11px] portal-text-muted mt-1">
                                    Akses file presentasi, modul fiqih sembelih, dan video pelatihan.
                                </p>
                            </div>

                            <a href="{{ route('participant.event.materials', $actEn->event->slug) }}"
                               class="inline-flex items-center justify-center gap-1.5 w-full h-9 px-3 rounded-xl portal-btn-outline font-semibold text-xs">
                                <span>Lihat Materi Kelas &rarr;</span>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </section>

    <!-- ========================================================= -->
    <!-- 5. KATALOG KEGIATAN YANG DIBUKA (MENDAFTAR KEGIATAN BARU) -->
    <!-- ========================================================= -->
    <section id="kegiatan-dibuka" aria-labelledby="heading-kegiatan-dibuka" class="space-y-3 pt-2">
        <div class="border-t portal-border pt-4 flex items-center justify-between">
            <div>
                <h2 id="heading-kegiatan-dibuka" class="text-base sm:text-lg font-bold portal-text flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 portal-text-muted"></i>
                    <span>Kegiatan yang Sedang Dibuka</span>
                </h2>
                <p class="text-xs portal-text-muted">
                    Pilih angkatan bimbingan teknis atau pelatihan yang ingin Anda ikuti.
                </p>
            </div>
        </div>

        @php
            $hasAnyOpenEvents = false;
        @endphp

        <div class="space-y-4">
            @foreach($eventTypes as $type)
                @php
                    $eventsOfType = $openEvents->get($type->value, collect());
                @endphp

                @if($eventsOfType->isNotEmpty())
                    @php $hasAnyOpenEvents = true; @endphp
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider portal-badge-neutral">
                                {{ $type->label() }}
                            </span>
                            <div class="h-px portal-border flex-grow"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($eventsOfType as $ev)
                                @php
                                    $isEnrolled = $myEnrollments->where('event_id', $ev->id)->first();
                                    $remaining = $ev->remaining_quota;
                                @endphp

                                <div class="portal-card p-4 flex flex-col justify-between space-y-3">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-xs font-semibold">
                                            <span class="{{ $remaining > 5 ? 'portal-text' : 'portal-text-muted' }}">
                                                Sisa: {{ $remaining }} peserta
                                            </span>
                                            <span class="portal-text-muted">
                                                {{ $ev->fee > 0 ? 'Rp ' . number_format($ev->fee, 0, ',', '.') : 'Gratis / Subsidi' }}
                                            </span>
                                        </div>

                                        <h3 class="font-bold text-sm sm:text-base portal-text leading-snug">
                                            {{ $ev->title }}
                                        </h3>

                                        <div class="text-xs portal-text-muted space-y-1 pt-0.5">
                                            <p class="flex items-center gap-1.5">
                                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                                <span>{{ $ev->starts_at?->translatedFormat('l, d F Y') }}</span>
                                            </p>
                                            <p class="flex items-center gap-1.5">
                                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                <span>{{ $ev->location }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-1">
                                        @if($isEnrolled)
                                            <button disabled class="w-full h-10 rounded-xl portal-surface-subtle portal-text-muted text-xs font-semibold cursor-not-allowed border portal-border">
                                                Sudah Terdaftar
                                            </button>
                                        @elseif($remaining <= 0)
                                            <button disabled class="w-full h-10 rounded-xl portal-badge-danger text-xs font-semibold cursor-not-allowed">
                                                Kuota Penuh
                                            </button>
                                        @else
                                            <a href="{{ route('enrollment.create', $ev->slug) }}"
                                               class="w-full h-10 inline-flex items-center justify-center gap-1.5 px-3 rounded-xl portal-btn-primary font-semibold text-xs shadow-xs">
                                                <span>Daftar Pelatihan &rarr;</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if(! $hasAnyOpenEvents)
                <div class="portal-card p-5 text-center text-xs sm:text-sm portal-text-muted">
                    Belum ada pendaftaran kegiatan bimtek yang sedang dibuka saat ini.
                </div>
            @endif
        </div>
    </section>

</div>
@endsection
