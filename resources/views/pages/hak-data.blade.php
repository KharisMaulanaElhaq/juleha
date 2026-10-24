@extends('layouts.app')

@section('title', 'Saluran Permintaan Hak Data Pribadi – DPD JULEHA Brebes')
@section('meta_description', 'Formulir resmi pengajuan hak data pribadi (akses, perbaikan, penghapusan/anonimisasi, portabilitas) kepada DPD JULEHA Kabupaten Brebes sesuai UU PDP.')

@section('content')
<!-- ===================== HERO FORM HAK DATA ===================== -->
<section class="relative py-14 sm:py-20 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wide mb-3 sm:mb-4">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Kepatuhan UU No. 27 Tahun 2022 (UU PDP)
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl md:text-5xl text-white mb-3 sm:mb-4 leading-tight">
            Saluran Permintaan Hak Data Pribadi
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed">
            Anda berhak mengetahui, memperbaiki, meminta salinan, atau mengajukan penghapusan data pribadi Anda yang dikelola oleh DPD JULEHA Kabupaten Brebes.
        </p>
    </div>
</section>

<!-- ===================== FORMULIR PENGAJUAN ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-8 p-6 rounded-3xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200">
                <div class="flex items-start gap-3">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h2 class="font-heading font-bold text-base sm:text-lg mb-1">Permohonan Berhasil Dikirimkan</h2>
                        <p class="text-xs sm:text-sm leading-relaxed">{{ session('success') }}</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-2">
                            Simpan nomor registrasi Anda untuk pengecekan status bersama petugas privasi kami.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid md:grid-cols-12 gap-8 items-start">
            <!-- Left Info Hak Subjek Data -->
            <div class="md:col-span-5 space-y-6">
                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-600 dark:text-slate-300 space-y-4">
                    <h2 class="font-heading font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                        Panduan Pengajuan
                    </h2>
                    <p class="leading-relaxed">
                        Kami menghargai privasi Anda dan menindaklanjuti setiap permohonan dalam waktu maksimal <strong>{{ $slaDays }} hari kalender</strong> sejak permohonan diverifikasi.
                    </p>
                    <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="font-semibold text-slate-800 dark:text-white">Tanpa Unggah Berkas KTP:</div>
                        <p class="text-xs leading-relaxed text-slate-500">
                            Untuk menjaga keamanan, formulir ini <strong>tidak meminta Anda mengunggah foto KTP</strong>. Petugas privasi kami akan memverifikasi identitas Anda secara manual melalui nomor WhatsApp atau alamat email resmi yang Anda daftarkan.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 text-xs">
                        <div class="font-semibold text-slate-800 dark:text-white mb-1">Sudah Memiliki Akun Portal?</div>
                        <p class="text-slate-500 mb-2">
                            Jika Anda terdaftar sebagai peserta, Anda dapat mengelola persetujuan dan mengunduh salinan data secara instan melalui Portal.
                        </p>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-semibold text-purple-600 dark:text-purple-400 hover:underline">
                            Masuk ke Portal Peserta <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-purple-50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 text-xs text-slate-600 dark:text-slate-300">
                    <span class="font-bold text-slate-800 dark:text-white block mb-1">Kontak Kepatuhan Privasi:</span>
                    <p>Petugas: {{ $privacyOfficer }}</p>
                    <p>Email: {{ $privacyEmail }}</p>
                </div>
            </div>

            <!-- Right Form Pengajuan -->
            <div class="md:col-span-7">
                <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-soft">
                    <h2 class="font-heading font-extrabold text-xl text-slate-900 dark:text-white mb-2">
                        Formulir Permohonan Hak Data
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                        Silakan isi data berikut secara benar untuk memudahkan verifikasi identitas oleh tim sekretariat.
                    </p>

                    <form method="POST" action="{{ route('data-rights.store') }}" class="space-y-4">
                        @csrf

                        <!-- Honeypot anti-bot (disembunyikan dari layar manusia) -->
                        <div style="display: none !important;" aria-hidden="true">
                            <label for="website_url">Jangan isi kolom ini</label>
                            <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Lengkap Pemohon *
                            </label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                   placeholder="Contoh: Muhammad Ihsan"
                                   class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                            @error('name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nomor WhatsApp atau Email Aktif *
                            </label>
                            <input type="text" name="contact" id="contact" required value="{{ old('contact') }}"
                                   placeholder="Contoh: 081234567890 atau email@anda.com"
                                   class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                            <p class="text-[11px] text-slate-400 mt-1">
                                Kontak ini akan digunakan untuk mengirimkan kode verifikasi dan bukti penyelesaian.
                            </p>
                            @error('contact')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="request_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Jenis Hak Data yang Diajukan *
                            </label>
                            <select name="request_type" id="request_type" required
                                    class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark">
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('request_type', $selectedType) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('request_type')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Uraian Permohonan *
                            </label>
                            <textarea name="description" id="description" rows="5" required
                                      placeholder="Jelaskan data apa yang ingin Anda akses, koreksi, atau hapus..."
                                      class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark">{{ old('description') }}</textarea>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Jelaskan secara ringkas dan jelas. Jangan mencantumkan kata sandi atau informasi rahasia perbankan.
                            </p>
                            @error('description')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn-primary w-full justify-center text-sm py-3 flex items-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Kirim Permohonan Hak Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
