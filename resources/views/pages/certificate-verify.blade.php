@extends('layouts.app')

@section('title', 'Verifikasi Catatan Sertifikat – ' . setting('site_name', 'DPD JULEHA Kabupaten Brebes'))
@section('meta_description', 'Layanan verifikasi catatan sertifikat kompetensi juru sembelih halal yang difasilitasi oleh DPD JULEHA Kabupaten Brebes.')

@push('head')
<meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<!-- ===================== HERO SECTION ===================== -->
<section class="relative pt-32 pb-16 bg-gradient-to-br from-brand-dark via-slate-900 to-brand-primary text-white overflow-hidden">
    <!-- Background Glow Effects -->
    <div class="absolute inset-0 pointer-events-none opacity-20">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-500 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-purple-500 blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-emerald-300">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            <span>Layanan Verifikasi Catatan Resmi</span>
        </div>

        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black font-heading tracking-tight leading-tight">
            Verifikasi Sertifikat Kompetensi
        </h1>

        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
            Periksa keabsahan catatan sertifikasi juru sembelih halal yang difasilitasi DPD JULEHA Kabupaten Brebes.
        </p>
    </div>
</section>

<!-- ===================== MAIN CONTENT SECTION ===================== -->
<section class="py-12 sm:py-16 bg-slate-50 dark:bg-slate-900 min-h-[60vh]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Banner Keabsahan Resmi LSP -->
        <div class="p-5 rounded-2xl bg-amber-500/10 dark:bg-amber-950/30 border border-amber-500/30 text-amber-900 dark:text-amber-200 text-xs sm:text-sm flex items-start gap-3.5">
            <i data-lucide="info" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
            <div class="space-y-1 leading-relaxed">
                <p class="font-bold">
                    Catatan Resmi DPD JULEHA Kabupaten Brebes
                </p>
                <p>
                    Halaman ini adalah catatan DPD JULEHA Kabupaten Brebes. Keabsahan akhir mengacu pada <strong>{{ $issuerName }}</strong>@if(!empty($issuerLicense)) (Nomor Lisensi: <span class="font-mono font-semibold">{{ $issuerLicense }}</span>)@endif.
                </p>
            </div>
        </div>

        <!-- Formulir Pencarian Nomor Sertifikat -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-800 shadow-xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="search" class="w-5 h-5 text-brand-primary dark:text-purple-400"></i>
                <span>Cari Catatan Nomor Sertifikat</span>
            </h2>

            <form action="{{ route('certificate.verify') }}" method="GET" class="space-y-3">
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="file-badge" class="w-5 h-5"></i>
                        </div>
                        <input type="text"
                               name="q"
                               value="{{ $queryNumber }}"
                               placeholder="Ketik nomor sertifikat (contoh: 001/LSP-P/BNSP/2026)"
                               required
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" />
                    </div>
                    <button type="submit"
                            class="px-6 py-3 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Periksa</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Format nomor sertifikat sensitif terhadap spasi dan simbol. Pastikan sama persis dengan yang tertera pada sertifikat fisik/digital.
                </p>
            </form>
        </div>

        <!-- HASIL: JIKA TIDAK DITEMUKAN / ERROR -->
        @if($errorMessage)
            <div class="p-6 rounded-3xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-center space-y-3" role="alert">
                <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
                    <i data-lucide="file-x" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-rose-900 dark:text-rose-200">
                    Hasil Pemeriksaan
                </h3>
                <p class="text-xs sm:text-sm text-rose-700 dark:text-rose-300 max-w-md mx-auto leading-relaxed">
                    {{ $errorMessage }}
                </p>
            </div>
        @endif

        <!-- HASIL: JIKA DITEMUKAN (KOMPETEN) -->
        @if($certificate)
            @php
                $isRevoked = $certificate->isRevoked();
                $isExpired = $certificate->isExpired();
                $isExpiringSoon = $certificate->isExpiringSoon();
                $isHolderErased = $certificate->isHolderDataErased();
                $showHolderName = (! $isSearchByNumber) && (! $isRevoked);

                // Status masa berlaku dihitung murni dari tanggal valid_until dan status pencabutan (UU PDP)
                $topBarBg = $isRevoked ? 'bg-red-600 text-white' : ($isExpired ? 'bg-rose-600 text-white' : ($isExpiringSoon ? 'bg-amber-600 text-white' : 'bg-emerald-600 text-white'));
                $borderCard = $isRevoked ? 'border-red-500' : ($isExpired ? 'border-rose-500' : ($isExpiringSoon ? 'border-amber-500' : 'border-emerald-500'));
            @endphp

            <div class="rounded-3xl bg-white dark:bg-slate-800 shadow-2xl border-2 {{ $borderCard }} overflow-hidden">
                
                <!-- Status Top Bar: Status Masa Berlaku & Keterangan Pemegang Terpisah -->
                <div class="px-6 py-4 {{ $topBarBg }} flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 font-bold text-sm uppercase tracking-wider">
                        <i data-lucide="{{ $isRevoked ? 'alert-triangle' : ($isExpired ? 'x-circle' : 'check-circle-2') }}" class="w-5 h-5"></i>
                        <span>Status Masa Berlaku: {{ $certificate->status_label }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($isHolderErased)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-md border border-white/30 text-white">
                                <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                                <span>Data Pemegang Telah Dihapus</span>
                            </span>
                        @endif
                        <span class="text-xs opacity-90 hidden sm:inline">Basis Data DPD JULEHA Brebes</span>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6">

                    <!-- Kartu Informasi Terverifikasi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-200 dark:border-slate-700">
                        @if($showHolderName)
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Nama Pemegang (Tersamar UU PDP)</span>
                                <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                                    <i data-lucide="{{ $isHolderErased ? 'user-minus' : 'user-check' }}" class="w-5 h-5 {{ $isHolderErased ? 'text-slate-500' : 'text-emerald-600 dark:text-emerald-400' }}"></i>
                                    <span>{{ $certificate->masked_holder_name }}</span>
                                </span>
                            </div>
                        @elseif($isSearchByNumber && ! $isRevoked)
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Identitas Pemegang</span>
                                <span class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 flex items-start gap-1.5 leading-relaxed">
                                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                                    <span>Nama pemegang hanya dapat diverifikasi melalui tautan / QR Code resmi sertifikat (UU PDP).</span>
                                </span>
                            </div>
                        @elseif($isRevoked)
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Status Catatan</span>
                                <span class="text-base font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <i data-lucide="ban" class="w-5 h-5"></i>
                                    <span>Sertifikat Telah Dicabut</span>
                                </span>
                            </div>
                        @endif

                        <div class="space-y-1">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block">Nomor Sertifikat</span>
                            <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white font-mono flex items-center gap-2">
                                <i data-lucide="badge-check" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                                <span>{{ $certificate->certificate_number }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block text-xs">Skema Kompetensi</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $certificate->scheme_name }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block text-xs">Lembaga Penerbit</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $certificate->issuer_name }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block text-xs">Tanggal Terbit</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $certificate->issued_at ? $certificate->issued_at->translatedFormat('d F Y') : '-' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block text-xs">Masa Berlaku Sampai</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $certificate->valid_until ? $certificate->valid_until->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                    </div>

                    @if($isRevoked)
                        <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-800 dark:text-red-200 space-y-1">
                            <p class="font-bold">Perhatian: Sertifikat ini telah dicabut.</p>
                            <p>Sertifikat dengan nomor ini telah dicabut dari catatan kepengurusan resmi DPD JULEHA Kabupaten Brebes dan tidak lagi berlaku.</p>
                            @if($certificate->revoked_at)
                                <p class="text-[11px] text-red-700 dark:text-red-300">Tanggal Pencabutan: {{ $certificate->revoked_at->translatedFormat('d F Y') }}</p>
                            @endif
                        </div>
                    @endif

                    @if($isHolderErased)
                        <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-xs text-slate-700 dark:text-slate-300 space-y-1">
                            <p class="font-bold flex items-center gap-1.5 text-slate-900 dark:text-white">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                <span>Keterangan Pelindungan Data Pribadi (UU PDP)</span>
                            </p>
                            <p>Data pemegang telah dihapus dari basis data aktif sesuai permohonan penghapusan data. Catatan nomor sertifikat dan masa berlakunya tetap dipertahankan untuk integritas verifikasi resmi.</p>
                        </div>
                    @endif

                    <!-- Catatan Kepatuhan Privasi (UU PDP) -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-start gap-2.5 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        <i data-lucide="shield" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                        <span>
                            Sesuai ketentuan UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP), identitas pemegang sertifikat dilindungi, nama disamarkan, dan data kontak/domisili tidak dipublikasikan ke publik.
                        </span>
                    </div>

                </div>
            </div>
        @endif

    </div>
</section>
@endsection
