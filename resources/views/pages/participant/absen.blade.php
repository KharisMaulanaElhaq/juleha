@extends('layouts.portal')

@section('title', 'Absen – ' . setting('site_name', 'DPD JULEHA Brebes'))
@section('meta_description', 'Halaman absen kegiatan pelatihan DPD JULEHA Kabupaten Brebes.')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl portal-surface border portal-border portal-text text-xs sm:text-sm font-semibold hover:portal-surface-subtle shadow-xs transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-[var(--primary)]"></i>
            <span>Kembali ke Beranda</span>
        </a>
        <div class="text-xs portal-text-muted">
            {{ now('Asia/Jakarta')->format('H.i') }} WIB
        </div>
    </div>

    @if($alreadyAttended)
        <!-- Layar Lembut Jika Sudah Pernah Absen Hari Ini -->
        <div class="portal-card p-6 sm:p-8 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl portal-badge-success flex items-center justify-center shadow-xs">
                <i data-lucide="check" class="w-8 h-8 stroke-[2.5]"></i>
            </div>
            <div class="space-y-1">
                <span class="inline-block text-[11px] font-bold uppercase tracking-wider portal-badge-success px-2.5 py-0.5 rounded-full">
                    Kehadiran Tercatat
                </span>
                <h1 class="text-xl sm:text-2xl font-bold portal-text">
                    Anda Sudah Absen Hari Ini
                </h1>
                <p class="text-xs sm:text-sm portal-text-muted mt-1 max-w-md mx-auto">
                    Kehadiran Anda pada sesi <strong class="portal-text">"{{ $todaySession->title ?? 'Sesi Hari Ini' }}"</strong> sudah tercatat pada pukul <strong class="portal-text">{{ $attendedTime ?? 'hari ini' }} WIB</strong>.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center gap-1.5 h-11 px-5 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition">
                    <span>Kembali ke Beranda</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    @else

        <!-- Kartu Utama Absen -->
        <div class="portal-card overflow-hidden">
            
            <!-- Info Sesi Kegiatan Ringkas -->
            <div class="p-4 sm:p-5 portal-surface-subtle border-b portal-border text-center space-y-1">
                <span class="inline-block text-[10px] font-bold uppercase tracking-wider portal-badge-neutral px-2.5 py-0.5 rounded-full">
                    Sesi Hari Ini
                </span>
                <h1 class="text-base sm:text-lg font-bold portal-text leading-snug">
                    {{ $todaySession?->title ?? 'Sesi Pelatihan JULEHA' }}
                </h1>
                <p class="text-xs portal-text-muted">
                    {{ $todaySession?->event?->title ?? 'DPD JULEHA Kabupaten Brebes' }} &bull; {{ $todaySession?->formatted_schedule ?? '' }} WIB
                </p>
            </div>

            <!-- Tab Switcher: Kamera vs Kode Angka -->
            <div class="p-4 pb-2">
                <div class="grid grid-cols-2 gap-1.5 p-1 portal-surface-subtle border portal-border rounded-xl">
                    <button type="button"
                            id="btnTabKamera"
                            onclick="switchTab('kamera')"
                            class="h-10 px-3 rounded-lg font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition {{ $activeTab === 'kamera' ? 'portal-btn-primary shadow-xs' : 'portal-text-muted hover:portal-text' }}">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>Pindai Kamera</span>
                    </button>
                    <button type="button"
                            id="btnTabKode"
                            onclick="switchTab('kode')"
                            class="h-10 px-3 rounded-lg font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition {{ $activeTab === 'kode' ? 'portal-btn-primary shadow-xs' : 'portal-text-muted hover:portal-text' }}">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                        <span>Ketik Kode Angka</span>
                    </button>
                </div>
            </div>

            <!-- Bagian 1: Pemindai Kamera Langsung -->
            <div id="sectionKamera" class="{{ $activeTab === 'kamera' ? 'block' : 'hidden' }} p-4 sm:p-5 pt-2 space-y-4">
                
                <div class="text-center space-y-0.5">
                    <h2 class="text-sm sm:text-base font-bold portal-text">
                        Arahkan kamera ke QR di layar kelas
                    </h2>
                    <span class="sr-only">Arahkan kamera ke layar di depan kelas</span>
                    <p class="text-xs portal-text-muted">
                        Posisikan kode QR di proyektor kelas ke dalam kotak bidik.
                    </p>
                </div>

                <!-- Viewfinder Pemindai Kamera -->
                <div class="relative bg-black rounded-xl overflow-hidden min-h-[280px] sm:min-h-[320px] flex items-center justify-center border portal-border shadow-inner">
                    <div id="reader" class="w-full h-full"></div>

                    <!-- Loading Kamera -->
                    <div id="cameraLoading" class="absolute inset-0 bg-slate-900 text-white flex flex-col items-center justify-center p-6 text-center space-y-2 z-10">
                        <div class="w-8 h-8 border-3 border-[var(--primary)] border-t-transparent rounded-full animate-spin"></div>
                        <p class="font-semibold text-xs sm:text-sm">Mempersiapkan Kamera...</p>
                        <p class="text-[11px] text-slate-400 max-w-xs">Ketuk "Izinkan" jika muncul permintaan izin kamera di peramban HP Anda.</p>
                    </div>

                    <!-- Penanganan Izin Ditolak -->
                    <div id="cameraErrorBox" class="hidden absolute inset-0 bg-slate-900/95 text-white flex flex-col items-center justify-center p-5 text-center space-y-3 z-20">
                        <div class="w-10 h-10 rounded-xl bg-rose-600/30 text-rose-400 flex items-center justify-center">
                            <i data-lucide="camera-off" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-white">Kamera Belum Diizinkan</h3>
                            <p class="text-xs text-slate-300 leading-relaxed max-w-xs">
                                Untuk mengaktifkan kamera, ketuk ikon gembok di sebelah alamat web browser lalu ubah izin Kamera menjadi "Izinkan".
                            </p>
                        </div>
                        <button type="button"
                                onclick="switchTab('kode')"
                                class="h-10 px-4 bg-white text-slate-900 hover:bg-slate-100 rounded-xl font-bold text-xs shadow-xs transition">
                            Gunakan Kode Angka Saja &rarr;
                        </button>
                    </div>
                </div>

                <!-- Tautan Jalur Cadangan -->
                <div class="space-y-2 pt-1 text-center">
                    <button type="button"
                            onclick="switchTab('kode')"
                            class="w-full h-11 px-4 rounded-xl portal-surface-subtle hover:bg-slate-200 dark:hover:bg-slate-800 border portal-border portal-text font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition">
                        <i data-lucide="keyboard" class="w-4 h-4 portal-text-muted"></i>
                        <span>Kamera tidak bisa? Isi kode angka</span>
                    </button>

                    <p class="text-xs portal-text-muted pt-1">
                        Masih bingung? <strong class="portal-text">Tunjukkan nama Anda ke panitia</strong> di meja kelas.
                    </p>
                </div>
            </div>

            <!-- Bagian 2: Input Kode Angka 6 Digit -->
            <div id="sectionKode" class="{{ $activeTab === 'kode' ? 'block' : 'hidden' }} p-4 sm:p-5 pt-2 space-y-4">
                
                <div class="text-center space-y-0.5">
                    <h2 class="text-sm sm:text-base font-bold portal-text">
                        Masukkan 6 Digit Kode Angka
                    </h2>
                    <p class="text-xs portal-text-muted">
                        Ketikkan kode 6 angka yang tampil di bawah QR pada layar proyektor.
                    </p>
                </div>

                @if(session('error'))
                    <div class="p-3 rounded-xl portal-badge-danger border portal-border text-xs font-medium flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('attendance.submit_code') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="inputCode" class="block text-xs font-semibold portal-text mb-1 text-center">
                            Kode 6 Angka Layar:
                        </label>
                        
                        <input type="text"
                               id="inputCode"
                               name="code"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="8"
                               placeholder="000 000"
                               autocomplete="off"
                               required
                               class="w-full text-center tracking-[0.25em] font-mono font-bold text-2xl sm:text-3xl h-14 px-4 rounded-xl border portal-border portal-surface portal-text focus:outline-none focus:ring-2 focus:ring-[var(--primary)] shadow-inner" />
                        
                        @error('code')
                            <p class="text-xs font-semibold text-[var(--danger-text)] text-center mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full h-12 rounded-xl portal-btn-primary font-bold text-sm shadow-sm transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-purple-400">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Kirim Kode Absen</span>
                    </button>
                </form>

                <!-- Pilihan Kembali ke Kamera -->
                <div class="pt-2 text-center border-t portal-border">
                    <button type="button"
                            onclick="switchTab('kamera')"
                            class="text-xs font-semibold text-[var(--primary)] hover:underline inline-flex items-center gap-1">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                        <span>Kembali ke pemindai kamera</span>
                    </button>
                </div>
            </div>

        </div>

    @endif

</div>
@endsection

@push('scripts')
<!-- Pustaka Pemindai QR Lokal Mandiri (html5-qrcode fixed version) -->
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script>
    let html5QrCode = null;
    let isCameraRunning = false;

    function switchTab(tab) {
        const secKamera = document.getElementById('sectionKamera');
        const secKode = document.getElementById('sectionKode');
        const btnTabKamera = document.getElementById('btnTabKamera');
        const btnTabKode = document.getElementById('btnTabKode');

        if (!secKamera || !secKode) return;

        if (tab === 'kamera') {
            secKamera.classList.remove('hidden');
            secKode.classList.add('hidden');
            btnTabKamera.className = 'h-10 px-3 rounded-lg font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition portal-btn-primary shadow-xs';
            btnTabKode.className = 'h-10 px-3 rounded-lg font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition portal-text-muted hover:portal-text';
            startScanner();
        } else {
            secKamera.classList.add('hidden');
            secKode.classList.remove('hidden');
            btnTabKode.className = 'h-10 px-3 rounded-lg font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition portal-btn-primary shadow-xs';
            btnTabKamera.className = 'h-10 px-3 rounded-lg font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition portal-text-muted hover:portal-text';
            stopScanner();
            const inputCode = document.getElementById('inputCode');
            if (inputCode) inputCode.focus();
        }
    }

    function onScanSuccess(decodedText) {
        if (!decodedText) return;
        
        stopScanner();

        const loadingBox = document.getElementById('cameraLoading');
        if (loadingBox) {
            loadingBox.classList.remove('hidden');
            loadingBox.innerHTML = '<div class="w-8 h-8 border-3 border-emerald-400 border-t-transparent rounded-full animate-spin"></div><p class="font-bold text-sm text-white">Kode Terbaca, Memproses...</p>';
        }

        if (decodedText.includes('/presensi/')) {
            window.location.href = decodedText;
        } else {
            window.location.href = '{{ url('/presensi') }}/' + encodeURIComponent(decodedText);
        }
    }

    function onScanFailure(error) {
        // Ignored during normal frame-by-frame scanning
    }

    function startScanner() {
        if (isCameraRunning) return;
        
        const loadingBox = document.getElementById('cameraLoading');
        const errorBox = document.getElementById('cameraErrorBox');
        if (loadingBox) loadingBox.classList.remove('hidden');
        if (errorBox) errorBox.classList.add('hidden');

        if (typeof Html5Qrcode === 'undefined') {
            console.error("Html5Qrcode library not loaded");
            if (loadingBox) loadingBox.classList.add('hidden');
            if (errorBox) errorBox.classList.remove('hidden');
            return;
        }

        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            const config = {
                fps: 10,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.0
            };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).then(() => {
                isCameraRunning = true;
                if (loadingBox) loadingBox.classList.add('hidden');
            }).catch(err => {
                console.warn("Kamera tidak bisa dibuka:", err);
                if (loadingBox) loadingBox.classList.add('hidden');
                if (errorBox) errorBox.classList.remove('hidden');
            });
        } catch (e) {
            console.error("Scanner exception:", e);
            if (loadingBox) loadingBox.classList.add('hidden');
            if (errorBox) errorBox.classList.remove('hidden');
        }
    }

    function stopScanner() {
        if (html5QrCode && isCameraRunning) {
            html5QrCode.stop().then(() => {
                isCameraRunning = false;
            }).catch(err => {
                isCameraRunning = false;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(! $alreadyAttended && $activeTab === 'kamera')
            startScanner();
        @endif
    });

    window.addEventListener('beforeunload', function() {
        stopScanner();
    });
</script>
@endpush
