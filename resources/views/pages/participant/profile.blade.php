@extends('layouts.portal')

@section('title', 'Lengkapi Profil Peserta – ' . setting('site_name', 'DPD JULEHA Brebes'))
@section('meta_description', 'Lengkapi data identitas peserta DPD JULEHA Kabupaten Brebes dalam 2 langkah mudah untuk pendaftaran kegiatan dan pencetakan sertifikat.')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman Profil & Perkiraan Durasi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-neutral">
                    <i data-lucide="clock" class="w-3.5 h-3.5 portal-text-muted"></i>
                    <span>Sekitar 2 menit</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($isCompleted ?? false) ? 'portal-badge-success' : 'portal-badge-warning' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($isCompleted ?? false) ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                    {{ ($isCompleted ?? false) ? 'Data Lengkap' : 'Wajib Dilengkapi' }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold portal-text tracking-tight">
                Lengkapi Profil Peserta
            </h1>
            <p class="text-xs sm:text-sm portal-text-muted mt-0.5">
                Data disimpan bertahap agar aman dan memudahkan pencetakan sertifikat resmi.
            </p>
        </div>

        <!-- Tab Pemilih Langkah Cepat (Bila Ingin Mengedit Bagian Tertentu) -->
        <div class="flex items-center gap-2 self-start sm:self-auto bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl text-xs font-semibold">
            <a href="{{ route('participant.profile', ['step' => 1]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $currentStep === 1 ? 'portal-btn-primary shadow-xs' : 'portal-text-muted hover:portal-text' }}">
                1. Kontak & Domisili
            </a>
            <a href="{{ route('participant.profile', ['step' => 2]) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $currentStep === 2 ? 'portal-btn-primary shadow-xs' : 'portal-text-muted hover:portal-text' }}">
                2. Data Sertifikat
            </a>
        </div>
    </div>

    <!-- Flash Notifikasi Ringkas -->
    @if(session('info'))
        <div class="p-3.5 rounded-xl portal-surface-subtle border portal-border portal-text text-xs sm:text-sm flex items-center gap-2.5 shadow-xs" role="status">
            <i data-lucide="info" class="w-4 h-4 portal-text-muted shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="p-3.5 rounded-xl border border-emerald-300 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 text-xs sm:text-sm flex items-center gap-2.5 shadow-xs" role="status">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 rounded-xl portal-badge-danger text-xs sm:text-sm flex items-center gap-2.5 shadow-xs" role="alert">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Indikator 2 Langkah Visual -->
    <div class="portal-card p-4 sm:p-5">
        <div class="flex items-center justify-between mb-2.5">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep === 1 ? 'portal-btn-primary' : 'bg-emerald-600 text-white' }}">
                    @if($currentStep === 2)
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    @else
                        1
                    @endif
                </span>
                <span class="text-xs sm:text-sm font-bold portal-text">
                    Langkah {{ $currentStep }} dari 2:
                    <span class="font-normal portal-text-muted">
                        {{ $currentStep === 1 ? 'Kontak dan Wilayah Domisili' : 'Data untuk Pencetakan Sertifikat' }}
                    </span>
                </span>
            </div>
            <span class="text-xs font-medium portal-text-muted">
                {{ $currentStep === 1 ? '50% selesai' : '100% selesai' }}
            </span>
        </div>
        
        <!-- Progress Bar Strip -->
        <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden flex">
            <div class="h-full bg-[var(--primary)] transition-all duration-300 {{ $currentStep === 1 ? 'w-1/2' : 'w-full' }}"></div>
        </div>
    </div>

    <!-- Formulir Profil Berdasarkan Langkah Aktif -->
    <div class="portal-card overflow-hidden">
        
        <!-- Bar Akun Terhubung -->
        <div class="px-5 py-3.5 portal-surface-subtle border-b portal-border flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->first_name }}" class="w-8 h-8 rounded-full object-cover border portal-border" />
                @else
                    <div class="w-8 h-8 rounded-full portal-icon-box font-bold text-xs flex items-center justify-center border portal-border">
                        {{ substr($user->first_name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="font-bold text-sm portal-text leading-tight truncate">{{ $user->name }}</div>
                    <div class="text-[11px] portal-text-muted truncate">{{ $user->email }}</div>
                </div>
            </div>
            <div class="text-xs portal-text-muted shrink-0 text-right">
                Peran: <strong class="portal-text font-semibold">Peserta</strong>
            </div>
        </div>

        <form action="{{ route('participant.profile.update') }}" method="POST" class="p-5 sm:p-6 space-y-6">
            @csrf
            <input type="hidden" name="step" value="{{ $currentStep }}">

            @if($currentStep === 1)
                <!-- ======================================================== -->
                <!-- LANGKAH 1: KONTAK DAN WILAYAH DOMISILI                  -->
                <!-- ======================================================== -->
                <div class="space-y-4">
                    <div class="pb-2 border-b portal-border">
                        <h2 class="text-base font-bold portal-text flex items-center gap-2">
                            <i data-lucide="phone-call" class="w-4 h-4 portal-text-muted"></i>
                            <span>Langkah 1: Kontak dan Domisili</span>
                        </h2>
                        <p class="text-xs portal-text-muted mt-0.5">
                            Data kontak untuk koordinasi grup pelatihan dan wilayah domisili di Kab. Brebes.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Nomor WhatsApp (input tel, auto-normalisasi) -->
                        <div class="sm:col-span-2">
                            <label for="phone-input" class="block text-xs font-semibold portal-text mb-1">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" id="phone-input" name="phone"
                                   value="{{ old('phone', $profile->phone ?? '') }}" required
                                   class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                   placeholder="0812xxxxxxxx" />
                            <p class="text-[11px] portal-text-muted mt-1">
                                Contoh: 081234567890 (otomatis dinormalisasi). Dipakai untuk koordinasi kelas dan sertifikasi.
                            </p>
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dropdown Kecamatan dari Tabel Resmi Kab. Brebes -->
                        <div>
                            <label for="district" class="block text-xs font-semibold portal-text mb-1">
                                Kecamatan (Kabupaten Brebes) <span class="text-rose-500">*</span>
                            </label>
                            <select id="district" name="district" required
                                    class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition">
                                <option value="" disabled {{ old('district', $profile->district ?? '') ? '' : 'selected' }}>Pilih kecamatan...</option>
                                @foreach($districts as $dist)
                                    <option value="{{ $dist }}" {{ old('district', $profile->district ?? '') === $dist ? 'selected' : '' }}>
                                        {{ $dist }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] portal-text-muted mt-1">
                                Pilih dari 17 kecamatan di Kab. Brebes.
                            </p>
                            @error('district')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Desa / Kelurahan -->
                        <div>
                            <label for="village" class="block text-xs font-semibold portal-text mb-1">
                                Desa / Kelurahan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="village" name="village"
                                   value="{{ old('village', $profile->village ?? '') }}" required
                                   class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                   placeholder="Contoh: Gandasuli / Dukuhturi" />
                            <p class="text-[11px] portal-text-muted mt-1">
                                Nama desa atau kelurahan tempat tinggal Anda.
                            </p>
                            @error('village')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Asal Instansi / Masjid (Opsional) -->
                        <div>
                            <label for="institution" class="block text-xs font-semibold portal-text mb-1">
                                Asal atau Instansi (Opsional)
                            </label>
                            <input type="text" id="institution" name="institution"
                                   value="{{ old('institution', $profile->institution ?? '') }}"
                                   class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                   placeholder="Contoh: DKM Masjid Agung / Pribadi" />
                            <p class="text-[11px] portal-text-muted mt-1">
                                Boleh dikosongkan jika mendaftar atas nama pribadi.
                            </p>
                            @error('institution')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status / Peran Peserta -->
                        <div>
                            <label for="status" class="block text-xs font-semibold portal-text mb-1">
                                Status / Kategori Peserta <span class="text-rose-500">*</span>
                            </label>
                            <select id="status" name="status" required
                                    class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition">
                                <option value="" disabled {{ old('status', $profile->status ?? '') ? '' : 'selected' }}>Pilih status peran...</option>
                                <option value="calon_juleha" {{ old('status', $profile->status ?? '') === 'calon_juleha' ? 'selected' : '' }}>Calon Juru Sembelih Halal (Pemula/Warga)</option>
                                <option value="juleha_aktif" {{ old('status', $profile->status ?? '') === 'juleha_aktif' ? 'selected' : '' }}>Juru Sembelih Halal Aktif (Praktisi)</option>
                                <option value="pengurus_masjid" {{ old('status', $profile->status ?? '') === 'pengurus_masjid' ? 'selected' : '' }}>Pengurus DKM / Panitia Kurban Masjid</option>
                                <option value="pekerja_rph" {{ old('status', $profile->status ?? '') === 'pekerja_rph' ? 'selected' : '' }}>Pekerja Rumah Potong Hewan (RPH/TPH)</option>
                            </select>
                            <p class="text-[11px] portal-text-muted mt-1">
                                Kategori ini memudahkan pembagian kelompok praktik.
                            </p>
                            @error('status')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi Langkah 1 -->
                    <div class="flex items-center justify-between gap-3 pt-4 border-t portal-border">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 text-xs sm:text-sm font-semibold portal-text-muted hover:portal-text transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="h-11 px-6 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-purple-400">
                            <span>Lanjut ke Langkah 2</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            @else
                <!-- ======================================================== -->
                <!-- LANGKAH 2: DATA UNTUK PENCETAKAN SERTIFIKAT             -->
                <!-- ======================================================== -->
                <div class="space-y-4">
                    <div class="pb-2 border-b portal-border">
                        <h2 class="text-base font-bold portal-text flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 portal-text-muted"></i>
                            <span>Langkah 2: Data untuk Sertifikat</span>
                        </h2>
                        <p class="text-xs portal-text-muted mt-0.5">
                            Data nama dan NIK digunakan untuk pencetakan sertifikat resmi pelatihan.
                        </p>
                    </div>

                    <div class="space-y-4 pt-1">
                        <!-- Nama Sesuai KTP (Awal terisi dari nama Google dan bisa diubah) -->
                        <div>
                            <label for="full_name_ktp" class="block text-xs font-semibold portal-text mb-1">
                                Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="full_name_ktp" name="full_name_ktp"
                                   value="{{ old('full_name_ktp', $profile->full_name_ktp ?? $user->name) }}" required
                                   class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                   placeholder="Contoh: AHMAD SUBHAN" />
                            <p class="text-[11px] portal-text-muted mt-1">
                                Nama lengkap tanpa singkatan sebagaimana akan dicetak pada sertifikat resmi.
                            </p>
                            @error('full_name_ktp')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NIK 16 Digit dengan Penghitung Langsung & Enkripsi -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="nik-input" class="block text-xs font-semibold portal-text">
                                    Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span>
                                </label>
                                @if(!$isNikLocked)
                                    <span id="nik-counter" class="text-xs font-mono font-semibold text-amber-600 dark:text-amber-400">
                                        0/16 digit
                                    </span>
                                @endif
                            </div>

                            @if($isNikLocked)
                                <div class="portal-surface-subtle p-3.5 rounded-xl border portal-border flex items-center justify-between">
                                    <div>
                                        <div class="font-mono text-sm font-bold portal-text">
                                            {{ $profile->masked_nik }}
                                        </div>
                                        <div class="text-[11px] portal-text-muted mt-0.5">
                                            NIK terkunci otomatis karena sertifikat resmi Anda telah diterbitkan.
                                        </div>
                                    </div>
                                    <span class="text-xs portal-text-muted flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3.5 h-3.5"></i> Terkunci
                                    </span>
                                </div>
                            @else
                                <input type="text" id="nik-input" name="nik"
                                       value="{{ old('nik', $profile->nik ?? '') }}"
                                       maxlength="16" inputmode="numeric"
                                       class="w-full h-11 px-3.5 rounded-xl border portal-border portal-surface portal-text text-sm font-mono tracking-wider focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent transition"
                                       placeholder="16 digit angka tanpa spasi (mis. 332901xxxxxxxxxx)"
                                       required />
                                
                                <p class="text-[11px] portal-text-muted mt-1.5 leading-relaxed">
                                    NIK dipakai hanya untuk pencetakan sertifikat dan disimpan terenkripsi. Hanya admin berwenang yang dapat melihatnya.
                                </p>

                                @error('nik')
                                    <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <!-- Kotak Persetujuan Data Pribadi -->
                        <div class="p-3.5 rounded-xl portal-surface-subtle border portal-border space-y-2">
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input type="checkbox" id="consent" name="consent" value="1"
                                       {{ old('consent', $profile->consent ?? false) ? 'checked' : '' }} required
                                       class="w-4 h-4 mt-0.5 rounded text-[var(--primary)] focus:ring-[var(--primary)] border portal-border" />
                                <span class="text-xs portal-text leading-relaxed">
                                    <strong class="font-semibold">Pernyataan Kebenaran Data:</strong>
                                    Saya menyatakan data di atas adalah benar dan menyetujui penggunaannya oleh DPD JULEHA Brebes untuk pencetakan sertifikat resmi.
                                </span>
                            </label>
                            @error('consent')
                                <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi Langkah 2 -->
                    <div class="flex items-center justify-between gap-3 pt-4 border-t portal-border">
                        <a href="{{ route('participant.profile', ['step' => 1]) }}"
                           class="h-11 px-4 rounded-xl portal-btn-subtle font-semibold text-xs sm:text-sm inline-flex items-center gap-2 transition">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali ke Langkah 1</span>
                        </a>
                        <button type="submit"
                                class="h-11 px-6 rounded-xl portal-btn-primary font-semibold text-xs sm:text-sm shadow-xs transition inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-purple-400">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Profil</span>
                        </button>
                    </div>
                </div>
            @endif
        </form>
    </div>

    <!-- ========================================================= -->
    <!-- RIWAYAT ABSEN PELATIHAN (KARTU RESPONSIF)                  -->
    <!-- ========================================================= -->
    <section id="riwayat" aria-labelledby="heading-riwayat" class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <div>
                <h2 id="heading-riwayat" class="text-base sm:text-lg font-bold portal-text flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 portal-text-muted"></i>
                    <span>Riwayat Absen Pelatihan</span>
                </h2>
                <p class="text-xs portal-text-muted">
                    Catatan kehadiran seluruh sesi pelatihan yang pernah Anda ikuti.
                </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full portal-badge-neutral">
                {{ isset($myAttendances) ? $myAttendances->count() : 0 }} Hadir
            </span>
        </div>

        @if(!isset($myAttendances) || $myAttendances->isEmpty())
            <div class="portal-card p-6 text-center text-xs sm:text-sm portal-text-muted">
                Belum ada riwayat absensi tercatat pada akun Anda.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($myAttendances as $attendance)
                    <div class="portal-card p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-bold text-sm portal-text leading-snug">
                                {{ $attendance->session->event->title ?? 'Kegiatan Pelatihan' }}
                            </div>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full portal-badge-success shrink-0">
                                Hadir
                            </span>
                        </div>
                        <div class="text-xs portal-text-muted space-y-1">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span>{{ $attendance->session->title ?? 'Sesi Pelatihan' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <span>{{ $attendance->attended_at ? $attendance->attended_at->translatedFormat('d F Y, H:i') : '-' }} WIB</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-normalisasi nomor WhatsApp pada blur (Langkah 1)
        const phoneInput = document.getElementById('phone-input');
        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                let val = this.value.trim().replace(/[^0-9+]/g, '');
                if (val.startsWith('+62')) {
                    val = '0' + val.substring(3);
                } else if (val.startsWith('62')) {
                    val = '0' + val.substring(2);
                }
                this.value = val;
            });
        }

        // Live counter dan validasi numerik NIK (Langkah 2)
        const nikInput = document.getElementById('nik-input');
        const nikCounter = document.getElementById('nik-counter');

        function updateNikCounter() {
            if (!nikInput || !nikCounter) return;
            // Hapus spasi dan karakter non-angka
            const raw = nikInput.value;
            const cleaned = raw.replace(/[^0-9]/g, '');
            if (raw !== cleaned) {
                nikInput.value = cleaned;
            }
            const len = cleaned.length;
            nikCounter.textContent = len + '/16 digit';
            if (len === 16) {
                nikCounter.className = 'text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1';
            } else {
                nikCounter.className = 'text-xs font-mono font-semibold text-amber-600 dark:text-amber-400';
            }
        }

        if (nikInput) {
            nikInput.addEventListener('input', updateNikCounter);
            nikInput.addEventListener('paste', function() {
                setTimeout(updateNikCounter, 10);
            });
            updateNikCounter();
        }
    });
</script>
@endsection
