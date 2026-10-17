@extends('layouts.app')

@section('title', 'Form Pendaftaran: ' . $event->title . ' – DPD JULEHA Brebes')
@section('meta_description', 'Pendaftaran online bimbingan teknis juru sembelih halal DPD JULEHA Kabupaten Brebes.')

@section('content')
<!-- ===================== HERO DAFTAR ===================== -->
<section class="relative py-16" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4">
            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i> Formulir Resmi
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl text-white mb-3">
            Pendaftaran {{ $event->title }}
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base">
            Silakan lengkapi identitas Anda di bawah ini dengan data yang valid untuk penerbitan administrasi kegiatan dan sertifikat.
        </p>
    </div>
</section>

<!-- ===================== FORM SECTION ===================== -->
<section class="section-padding bg-slate-50 dark:bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Event Summary Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-slate-700 mb-8" data-aos="fade-up">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100 dark:border-slate-700">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand dark:text-brand-dark">Jadwal Terpilih</span>
                    <h2 class="font-heading font-bold text-xl sm:text-2xl text-slate-800 dark:text-white mt-1">{{ $event->title }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    @if ($event->isOpenForRegistration())
                        <span class="inline-flex items-center gap-1.5 bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark text-xs font-bold px-3 py-1.5 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>
                            Pendaftaran Dibuka
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 text-xs font-bold px-3 py-1.5 rounded-full">
                            Pendaftaran Ditutup
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 text-xs sm:text-sm">
                <div>
                    <span class="text-slate-400 block mb-0.5">Waktu Pelaksanaan</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $event->starts_at->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Lokasi Kegiatan</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $event->location }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Sisa Kuota</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $event->remaining_quota }} dari {{ $event->quota }} kursi</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Kontribusi / Biaya</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ $event->fee > 0 ? 'Rp ' . number_format($event->fee, 0, ',', '.') : 'Gratis / Disubsidi' }}
                    </span>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                <div class="font-bold mb-2 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    Terdapat kesalahan pada isian formulir:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $event->isOpenForRegistration())
            <div class="p-8 text-center bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700">
                <div class="w-16 h-16 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="lock" class="w-8 h-8"></i>
                </div>
                <h3 class="font-heading font-bold text-xl text-slate-800 dark:text-white mb-2">Pendaftaran Sedang Ditutup</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm max-w-md mx-auto mb-6">
                    Mohon maaf, kuota untuk jadwal ini telah terpenuhi atau pendaftaran telah ditutup oleh panitia. Silakan pantau jadwal bimtek angkatan berikutnya.
                </p>
                <a href="{{ route('bimtek.index') }}" class="btn-primary text-xs py-2.5 px-5">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Lihat Jadwal Lainnya
                </a>
            </div>
        @else
            <!-- Registration Form Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100 dark:border-slate-700" data-aos="fade-up">
                <form action="{{ route('bimtek.daftar.store', $event->slug) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Honeypot field (hidden from legitimate users) -->
                    <div style="display: none !important;">
                        <label for="website_hp">Jangan isi kolom ini jika Anda manusia</label>
                        <input type="text" name="website_hp" id="website_hp" value="" tabindex="-1" autocomplete="off" />
                    </div>

                    <!-- Row 1: Nama Lengkap -->
                    <div>
                        <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                            Nama Lengkap (Beserta Gelar jika ada) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="full_name" id="full_name" required value="{{ old('full_name') }}"
                            placeholder="Contoh: H. Ahmad Fauzi, S.Pd."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                        <span class="text-[11px] text-slate-400 mt-1 block">Nama ini akan dicetak pada piagam/sertifikat bimtek.</span>
                    </div>

                    <!-- Row 2: WhatsApp & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                            <span class="text-[11px] text-slate-400 mt-1 block">Digunakan untuk konfirmasi dan masuk grup koordinasi.</span>
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                                Alamat Email (Opsional)
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                            <span class="text-[11px] text-slate-400 mt-1 block">Untuk menerima salinan bukti pendaftaran digital.</span>
                        </div>
                    </div>

                    <!-- Row 3: Domisili Kecamatan & Desa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="district" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                                Asal Kecamatan <span class="text-rose-500">*</span>
                            </label>
                            <select name="district" id="district" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark">
                                <option value="" disabled {{ old('district') ? '' : 'selected' }}>-- Pilih Kecamatan di Brebes --</option>
                                @foreach ($districts as $d)
                                    <option value="{{ $d }}" {{ old('district') === $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="village" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                                Nama Desa / Kelurahan
                            </label>
                            <input type="text" name="village" id="village" value="{{ old('village') }}"
                                placeholder="Contoh: Desa Gandasuli"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                        </div>
                    </div>

                    <!-- Row 4: Kategori Peserta -->
                    <div>
                        <label for="participant_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                            Kategori / Latar Belakang Peserta <span class="text-rose-500">*</span>
                        </label>
                        <select name="participant_type" id="participant_type" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark">
                            <option value="" disabled {{ old('participant_type') ? '' : 'selected' }}>-- Pilih Kategori Peserta --</option>
                            @foreach ($participantTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('participant_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Row 5: Catatan -->
                    <div>
                        <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                            Catatan / Harapan Mengikuti Pelatihan (Opsional)
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            placeholder="Contoh: Ingin memperdalam teknik asah bilah dan fiqih penyembelihan unggas..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Row 6: Persetujuan Data Pribadi (UU PDP) -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-brand-light/60 dark:bg-brand-hero-dark/30 border border-brand/20 dark:border-brand-dark/20">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="consent" id="consent" value="1" required {{ old('consent') ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded text-brand focus:ring-brand border-gray-300" />
                            <span class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                <strong>Persetujuan Pelindungan Data Pribadi:</strong> Saya menyatakan bahwa data yang diisikan adalah benar. Saya memberikan persetujuan kepada DPD JULEHA Kabupaten Brebes untuk menggunakan data ini demi keperluan administrasi bimtek, penerbitan sertifikat, dan komunikasi kegiatan organisasi sesuai <a href="{{ route('kebijakan-privasi') }}" target="_blank" class="text-brand dark:text-brand-dark underline font-semibold">Kebijakan Privasi</a>. <span class="text-rose-500 font-bold">*</span>
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full justify-center py-4 text-base font-bold shadow-lg">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            Kirim Formulir Pendaftaran Bimtek
                        </button>
                        <p class="text-center text-xs text-slate-400 mt-3">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 inline text-brand dark:text-brand-dark mr-1"></i>
                            Proses pendaftaran dilindungi protokol keamanan dan verifikasi data resmi panitia.
                        </p>
                    </div>
                </form>
            </div>
        @endif
    </div>
</section>
@endsection
