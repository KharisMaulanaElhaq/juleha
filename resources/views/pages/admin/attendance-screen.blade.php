<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layar Presensi QR: {{ $session->title }} – DPD JULEHA Brebes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
        #qrcode svg, #qrcode canvas, #qrcode img {
            margin: 0 auto;
            display: block;
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="bg-[#120a1f] text-slate-100 min-h-screen flex flex-col justify-between selection:bg-purple-500 selection:text-white overflow-x-hidden">

    <!-- Top Bar: Navigasi & Info Sesi -->
    <header class="w-full bg-[#1e1133]/90 backdrop-blur border-b border-purple-900/40 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-600 to-amber-500 flex items-center justify-center shadow-lg shadow-purple-950/50 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
            </div>
            <div>
                <span class="text-xs uppercase tracking-widest text-amber-400 font-bold block">
                    DPD JULEHA KABUPATEN BREBES &bull; LAYAR PROYEKTOR
                </span>
                <h1 class="text-lg md:text-xl font-display font-bold text-white leading-tight">
                    {{ $session->title }}
                </h1>
                <p class="text-xs text-purple-300/80">
                    {{ $event->title ?? '-' }} &bull; {{ $session->location ?? 'Brebes' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Toggle Status Buka/Tutup Presensi -->
            <button id="btnToggleOpen"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border {{ $session->attendance_open ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30' }}">
                <span class="w-2 h-2 rounded-full {{ $session->attendance_open ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                <span id="labelStatusOpen">{{ $session->attendance_open ? 'Presensi: DIBUKA' : 'Presensi: SESUAI JAM' }}</span>
            </button>

            <!-- Tombol Fullscreen -->
            <button id="btnFullscreen" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white transition flex items-center gap-1.5 border border-white/10">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                </svg>
                <span>Layar Penuh</span>
            </button>

            <!-- Tombol Tutup / Kembali -->
            <a href="{{ url('/admin/events/' . $session->event_id . '/edit') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-purple-950 hover:bg-purple-900 text-purple-200 transition border border-purple-800">
                &larr; Kembali ke Admin
            </a>
        </div>
    </header>

    <!-- Main Content: Center QR Code Card -->
    <main class="flex-1 flex flex-col lg:flex-row items-center justify-center p-6 md:p-10 gap-8 max-w-7xl mx-auto w-full">
        
        <!-- Left / Center: QR Code Utama -->
        <div class="flex flex-col items-center text-center space-y-6">
            <div class="relative group">
                <!-- Glowing Ring Aura -->
                <div class="absolute -inset-4 bg-gradient-to-r from-purple-600 via-amber-500 to-purple-600 rounded-3xl blur-xl opacity-40 group-hover:opacity-70 transition duration-1000 animate-pulse"></div>

                <!-- QR Container Box -->
                <div class="relative bg-white p-6 md:p-8 rounded-3xl shadow-2xl border-4 border-amber-400/80 flex flex-col items-center">
                    <div id="qrcode" class="w-[280px] h-[280px] sm:w-[320px] sm:h-[320px] md:w-[360px] md:h-[360px] flex items-center justify-center"></div>

                    <!-- Watermark Logo Label & Direct URL Testing -->
                    <div class="mt-4 pt-3 border-t border-slate-100 w-full flex flex-col gap-2">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                            <span>DPD JULEHA BREBES</span>
                            <span class="text-purple-700 font-mono">SCAN VIA KAMERA HP</span>
                        </div>
                        @if(app()->environment('local'))
                            <div class="flex items-center gap-2 pt-1">
                                <input id="inputTokenUrl" type="text" readonly class="text-[11px] font-mono text-slate-600 bg-slate-100 px-2.5 py-1.5 rounded-lg border border-slate-200 w-full truncate select-all" value="{{ $initialToken['url'] ?? '' }}" title="Tautan presensi saat ini (Mode Pengujian Lokal)">
                                <button id="btnCopyUrl" type="button" class="px-3 py-1.5 text-xs font-semibold text-purple-700 bg-purple-100 hover:bg-purple-200 rounded-lg border border-purple-300 whitespace-nowrap transition flex items-center gap-1">
                                    <span id="copyBtnText">Salin Tautan</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kode Angka 6 Digit Besar -->
            <div class="w-full max-w-md bg-[#1e1133] rounded-3xl p-5 border-2 border-amber-400/80 shadow-2xl text-center space-y-2">
                <span class="text-xs uppercase tracking-widest text-amber-300 font-bold block">
                    KODE ABSEN 6 DIGIT
                </span>
                <div id="displayNumericCode" class="text-4xl sm:text-5xl font-mono font-extrabold text-amber-400 tracking-[0.25em] py-1 select-all">
                    {{ $initialToken['formatted_code'] ?? '000 000' }}
                </div>
                <p class="text-xs text-purple-200 font-medium pt-1 border-t border-purple-800/60">
                    Buka situs &rarr; Absen Sekarang &rarr; arahkan kamera, atau ketik kode angka di atas
                </p>
            </div>

            <!-- Countdown Timer & Refresh Bar -->
            <div class="w-full max-w-md bg-[#1e1133] rounded-2xl p-4 border border-purple-800/50 shadow-lg space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-purple-300 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Kode Otomatis Berganti
                    </span>
                    <span id="countdownText" class="font-mono font-bold text-amber-400">45 dtk</span>
                </div>
                <div class="w-full bg-purple-950 rounded-full h-2 overflow-hidden border border-purple-900">
                    <div id="countdownBar" class="bg-gradient-to-r from-purple-500 to-amber-400 h-full w-full transition-all duration-1000"></div>
                </div>
                <p class="text-[11px] text-purple-300/70 text-center pt-1">
                    Kode kedaluwarsa cepat mencegah kecurangan titip absen.
                </p>
            </div>
        </div>

        <!-- Right: Panel Realtime Kehadiran & Petunjuk -->
        <div class="w-full lg:w-96 space-y-6">
            
            <!-- Card Counter Hadir Realtime -->
            <div class="bg-gradient-to-br from-[#24133d] to-[#170c29] p-6 rounded-3xl border border-purple-700/40 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-purple-300 font-semibold">Total Kehadiran</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">Live Polling</span>
                </div>

                <div class="flex items-baseline gap-3">
                    <span id="counterHadir" class="text-5xl md:text-6xl font-display font-extrabold text-amber-400">
                        {{ $initialToken['hadir_count'] ?? 0 }}
                    </span>
                    <span class="text-lg text-purple-300">
                        / <span id="counterTotal">{{ $totalActiveEnrollments }}</span> Peserta
                    </span>
                </div>

                <!-- Progress Bar Persentase Hadir -->
                <div class="w-full bg-purple-950/80 rounded-full h-2.5 overflow-hidden border border-purple-800">
                    <div id="persentaseBar"
                         class="bg-emerald-500 h-full transition-all duration-500"
                         style="width: {{ $totalActiveEnrollments > 0 ? min(100, round(($initialToken['hadir_count'] / $totalActiveEnrollments) * 100)) : 0 }}%;"></div>
                </div>
                <div class="flex justify-between text-xs text-purple-300/80 font-mono">
                    <span>Tingkat Partisipasi</span>
                    <span id="persentaseText">{{ $totalActiveEnrollments > 0 ? round(($initialToken['hadir_count'] / $totalActiveEnrollments) * 100) : 0 }}%</span>
                </div>
            </div>

            <!-- Card Petunjuk Peserta -->
            <div class="bg-[#1e1133]/80 p-5 rounded-2xl border border-purple-800/40 text-xs space-y-3">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Petunjuk Peserta Pelatihan:
                </h3>
                <ol class="list-decimal list-inside space-y-1.5 text-purple-200/90 leading-relaxed">
                    <li>Buka aplikasi kamera bawaan HP Anda.</li>
                    <li>Arahkan lensa ke kode QR di layar proyektor.</li>
                    <li>Ketuk notifikasi tautan browser yang muncul.</li>
                    <li>Masuk (Login) jika belum, sistem mencatat otomatis.</li>
                </ol>
            </div>

            <!-- Live Feed Peserta yang Baru Masuk -->
            <div class="bg-[#1e1133]/80 p-5 rounded-2xl border border-purple-800/40 text-xs space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-300 block">
                    Presensi Terkini:
                </span>
                <div id="recentList" class="space-y-2">
                    <p class="text-purple-400/60 italic text-center py-2">Menunggu pemindaian peserta...</p>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer Bar -->
    <footer class="w-full bg-[#160b26] border-t border-purple-900/30 px-6 py-3 text-center text-xs text-purple-300/70 flex flex-wrap items-center justify-between gap-2">
        <span>Zona Waktu: <strong>WIB (Asia/Jakarta)</strong></span>
        <span>Sistem Presensi Berbasis QR Dinamis DPD JULEHA Brebes &bull; Aman & Akurat</span>
        <span id="currentTime" class="font-mono text-amber-400 font-bold">--:--:-- WIB</span>
    </footer>

    <!-- Standalone QR Code Library -->
    <script src="{{ asset('js/qrcode.min.js') }}"></script>

    <script>
        const sessionId = {{ $session->id }};
        const tokenEndpoint = "{{ route('admin.attendance.screen.token', $session->id) }}";
        const statsEndpoint = "{{ route('admin.attendance.screen.stats', $session->id) }}";
        const toggleEndpoint = "{{ route('admin.attendance.screen.toggle', $session->id) }}";
        const csrfToken = "{{ csrf_token() }}";

        let currentUrl = "{{ $initialToken['url'] }}";
        let countdownSeconds = 45;
        const refreshCycle = 45;
        let qrCodeInstance = null;

        // Inisialisasi QR Code
        function initQrCode(text) {
            const container = document.getElementById("qrcode");
            container.innerHTML = "";
            qrCodeInstance = new QRCode(container, {
                text: text,
                width: 320,
                height: 320,
                colorDark: "#1a0f2b",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }

        // Perbarui QR Code
        function updateQrCode(text) {
            if (qrCodeInstance) {
                qrCodeInstance.makeCode(text);
            } else {
                initQrCode(text);
            }
        }

        // Ambil Token Baru dari Server
        async function fetchNewToken() {
            try {
                const response = await fetch(tokenEndpoint, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data.url) {
                        currentUrl = result.data.url;
                        updateQrCode(currentUrl);
                        const urlInput = document.getElementById('inputTokenUrl');
                        if (urlInput) urlInput.value = currentUrl;
                        if (result.data.formatted_code) {
                            const codeEl = document.getElementById('displayNumericCode');
                            if (codeEl) codeEl.innerText = result.data.formatted_code;
                        }
                        countdownSeconds = refreshCycle;
                        updateCountdownUI();
                    }
                }
            } catch (err) {
                console.error("Gagal mengambil token baru:", err);
            }
        }

        // Polling Statistik Kehadiran
        async function fetchStats() {
            try {
                const response = await fetch(statsEndpoint, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        document.getElementById('counterHadir').innerText = data.hadir_count;
                        document.getElementById('counterTotal').innerText = data.total_enrollments;

                        const pct = data.total_enrollments > 0
                            ? Math.min(100, Math.round((data.hadir_count / data.total_enrollments) * 100))
                            : 0;
                        document.getElementById('persentaseBar').style.width = pct + '%';
                        document.getElementById('persentaseText').innerText = pct + '%';

                        // Render recent attendees
                        if (data.recent && data.recent.length > 0) {
                            const listHtml = data.recent.map(r => `
                                <div class="flex items-center justify-between p-2 rounded-lg bg-purple-950/40 border border-purple-800/30">
                                    <span class="font-semibold text-white truncate max-w-[170px]">${r.name}</span>
                                    <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">${r.time} WIB</span>
                                </div>
                            `).join('');
                            document.getElementById('recentList').innerHTML = listHtml;
                        }
                    }
                }
            } catch (err) {
                console.error("Gagal mengambil statistik:", err);
            }
        }

        // Countdown Timer Loop (1 detik)
        function updateCountdownUI() {
            document.getElementById('countdownText').innerText = countdownSeconds + ' dtk';
            const pct = (countdownSeconds / refreshCycle) * 100;
            document.getElementById('countdownBar').style.width = pct + '%';
        }

        setInterval(() => {
            countdownSeconds--;
            if (countdownSeconds <= 0) {
                fetchNewToken();
            } else {
                updateCountdownUI();
            }
        }, 1000);

        // Polling Statistik tiap 4 detik
        setInterval(fetchStats, 4000);

        // Jam Realtime WIB
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta' }) + ' WIB';
            document.getElementById('currentTime').innerText = timeStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Toggle Fullscreen
        document.getElementById('btnFullscreen').addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => alert("Tidak dapat masuk layar penuh"));
            } else {
                document.exitFullscreen();
            }
        });

        // Toggle Buka/Tutup Presensi
        document.getElementById('btnToggleOpen').addEventListener('click', async () => {
            try {
                const res = await fetch(toggleEndpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                if (json.success) {
                    const btn = document.getElementById('btnToggleOpen');
                    const label = document.getElementById('labelStatusOpen');
                    if (json.attendance_open) {
                        btn.className = "px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border bg-emerald-500/20 text-emerald-300 border-emerald-500/30";
                        label.innerText = "Presensi: DIBUKA";
                    } else {
                        btn.className = "px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border bg-amber-500/20 text-amber-300 border-amber-500/30";
                        label.innerText = "Presensi: SESUAI JAM";
                    }
                }
            } catch (err) {
                console.error("Gagal toggle status:", err);
            }
        });

        // Copy URL Helper untuk Admin / Pengujian
        const btnCopyUrl = document.getElementById('btnCopyUrl');
        if (btnCopyUrl) {
            btnCopyUrl.addEventListener('click', () => {
                const urlInput = document.getElementById('inputTokenUrl');
                if (urlInput && urlInput.value) {
                    navigator.clipboard.writeText(urlInput.value).then(() => {
                        const copyText = document.getElementById('copyBtnText');
                        if (copyText) copyText.innerText = 'Tersalin!';
                        setTimeout(() => {
                            if (copyText) copyText.innerText = 'Salin Tautan';
                        }, 2000);
                    }).catch(() => {
                        urlInput.select();
                        document.execCommand('copy');
                        const copyText = document.getElementById('copyBtnText');
                        if (copyText) copyText.innerText = 'Tersalin!';
                        setTimeout(() => {
                            if (copyText) copyText.innerText = 'Salin Tautan';
                        }, 2000);
                    });
                }
            });
        }

        // Inisialisasi awal
        window.addEventListener('DOMContentLoaded', () => {
            initQrCode(currentUrl);
            updateCountdownUI();
        });
    </script>
</body>
</html>
