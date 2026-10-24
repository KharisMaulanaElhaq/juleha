@extends('layouts.portal')

@section('title', 'Privasi & Data Saya – Portal Peserta DPD JULEHA Brebes')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-purple-900/30">
        <div>
            <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-purple-400 bg-purple-950/60 px-3 py-1 rounded-full border border-purple-800/50 mb-2">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                Kepatuhan UU Perlindungan Data Pribadi
            </div>
            <h1 class="text-2xl sm:text-3xl font-heading font-extrabold text-white">
                Privasi dan Data Saya
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                Ketahui data yang dikumpulkan sistem, kendalikan persetujuan per tujuan, dan gunakan hak privasi Anda secara transparan.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('participant.privacy.download') }}" class="btn-outline text-xs py-2 px-3 flex items-center gap-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Unduh Salinan Data (.JSON)
            </a>
            <a href="{{ route('data-rights.portal') }}" class="btn-primary text-xs py-2 px-3 flex items-center gap-1.5">
                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                Ajukan Hak Data
            </a>
        </div>
    </div>

    <!-- Alert Status -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('warning'))
        <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-800/60 text-amber-300 text-xs sm:text-sm flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-400 flex-shrink-0"></i>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    <!-- SECTION 1: PERSETUJUAN PER TUJUAN (5 TUJUAN) -->
    <div class="portal-card p-6 sm:p-8 rounded-3xl space-y-6">
        <div>
            <h2 class="text-lg sm:text-xl font-heading font-bold text-white flex items-center gap-2">
                <i data-lucide="list-checks" class="w-5 h-5 text-purple-400"></i>
                Daftar Persetujuan Pemrosesan Data (5 Tujuan)
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                Tiap kotak memiliki tujuan dan konsekuensi yang terpisah. Anda memiliki hak untuk menarik atau memberikan kembali persetujuan Anda kapan saja.
            </p>
        </div>

        <div class="space-y-4">
            @foreach($consents as $item)
                <div class="p-5 rounded-2xl border transition-colors {{ $item['is_granted'] ? 'bg-slate-800/60 border-purple-800/40' : 'bg-slate-900/40 border-slate-800 text-slate-400' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-heading font-bold text-sm sm:text-base text-white">
                                    {{ $item['name'] }}
                                </span>
                                @if($item['is_mandatory'])
                                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-purple-900/60 text-purple-300 border border-purple-700/50">
                                        Wajib untuk Pelatihan
                                    </span>
                                @else
                                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50">
                                        Opsional
                                    </span>
                                @endif

                                @if($item['is_granted'])
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-900/60 text-emerald-300 border border-emerald-700/50 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Aktif Disetujui
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 border border-slate-700">
                                        Tidak Aktif / Ditarik
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                {{ $item['description'] }}
                            </p>

                            @if(! $item['is_granted'])
                                <p class="text-xs text-amber-400/90 italic">
                                    Konsekuensi: {{ $item['consequence_if_withdrawn'] }}
                                </p>
                            @endif

                            <div class="text-[11px] text-slate-400 pt-1">
                                @if($item['is_granted'] && $item['granted_at'])
                                    Diberikan pada: {{ $item['granted_at']->format('d M Y, H:i') }} WIB (v{{ $item['version'] }})
                                @elseif($item['withdrawn_at'])
                                    Ditarik pada: {{ $item['withdrawn_at']->format('d M Y, H:i') }} WIB
                                @endif
                            </div>
                        </div>

                        <!-- Toggle Button -->
                        <div class="flex-shrink-0 pt-1">
                            @if($item['is_granted'])
                                <form method="POST" action="{{ route('participant.privacy.withdraw') }}" onsubmit="return confirm('{{ $item['is_mandatory'] ? 'PERHATIAN: Ini adalah persetujuan wajib. Menariknya akan memblokir pendaftaran kegiatan Anda. Lanjutkan penarikan persetujuan?' : 'Tarik persetujuan ini?' }}')">
                                    @csrf
                                    <input type="hidden" name="purpose" value="{{ $item['purpose'] }}">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 border border-rose-800/60 transition-colors">
                                        Tarik Persetujuan
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('participant.privacy.grant') }}">
                                    @csrf
                                    <input type="hidden" name="purpose" value="{{ $item['purpose'] }}">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-950/60 hover:bg-emerald-900/80 text-emerald-300 border border-emerald-800/60 transition-colors">
                                        Berikan Persetujuan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECTION 2: RINGKASAN DATA PRIBADI SAYA -->
    <div class="portal-card p-6 sm:p-8 rounded-3xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg sm:text-xl font-heading font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-purple-400"></i>
                    Data Pribadi yang Tersimpan Tentang Saya
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Berikut adalah ikhtisar data Anda yang disimpan dalam sistem basis data aman DPD JULEHA Brebes.
                </p>
            </div>
            <a href="{{ route('participant.profile') }}" class="btn-outline text-xs py-1.5 px-3 self-start">
                Perbarui Profil
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs sm:text-sm">
            <div class="p-4 rounded-2xl bg-slate-800/40 border border-purple-900/20">
                <div class="text-slate-400 text-xs">Nama Lengkap KTP</div>
                <div class="font-bold text-white mt-1">{{ $profile?->full_name_ktp ?? $user->name }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/40 border border-purple-900/20">
                <div class="text-slate-400 text-xs">Nomor Induk Kependudukan (NIK)</div>
                <div class="font-mono font-bold text-purple-300 mt-1">
                    {{ $profile?->masked_nik ?? 'Belum dilengkapi' }}
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">Tersamar demi privasi &amp; terenkripsi</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/40 border border-purple-900/20">
                <div class="text-slate-400 text-xs">Nomor WhatsApp Aktif</div>
                <div class="font-bold text-white mt-1">{{ $profile?->formatted_phone ?? $user->phone ?? '-' }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/40 border border-purple-900/20">
                <div class="text-slate-400 text-xs">Alamat Email Terdaftar</div>
                <div class="font-bold text-white mt-1">{{ $user->email }}</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/40 border border-purple-900/20">
                <div class="text-slate-400 text-xs">Domisili Kecamatan &amp; Desa</div>
                <div class="font-bold text-white mt-1">
                    {{ $profile?->village ? $profile->village . ', ' : '' }}{{ $profile?->district ?? '-' }}
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/40 border border-purple-900/20">
                <div class="text-slate-400 text-xs">Asal Instansi / Masjid</div>
                <div class="font-bold text-white mt-1">{{ $profile?->institution ?? '-' }}</div>
            </div>
        </div>

        <div class="pt-4 border-t border-purple-900/30 flex flex-wrap items-center justify-between gap-4">
            <div class="text-xs text-slate-400 flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-emerald-400"></i>
                Data sensitif dilindungi dengan enkripsi AES dan akses terbatas petugas resmi.
            </div>
            <a href="{{ route('participant.privacy.download') }}" class="btn-primary text-xs py-2 px-4 flex items-center gap-2">
                <i data-lucide="file-json" class="w-4 h-4"></i>
                Unduh Salinan Lengkap Data Saya (.JSON)
            </a>
        </div>
    </div>

    <!-- SECTION 3: SALURAN HAK SUBJEK DATA & PERMOHONAN HAPUS AKUN -->
    <div class="portal-card p-6 sm:p-8 rounded-3xl space-y-4 border border-rose-900/30">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="space-y-2">
                <h2 class="text-lg sm:text-xl font-heading font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-x" class="w-5 h-5 text-rose-400"></i>
                    Permohonan Penghapusan / Anonimisasi Akun (UU PDP)
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl">
                    Sesuai dengan ketentuan UU No. 27 Tahun 2022, Anda berhak mengajukan permohonan penghapusan atau anonimisasi seluruh identitas pribadi Anda dari sistem DPD JULEHA Brebes.
                </p>
                <div class="text-xs text-slate-400 space-y-1">
                    <p>&bull; Jika Anda belum memiliki riwayat kegiatan, akun Anda akan dihapus secara penuh.</p>
                    <p>&bull; Jika Anda telah mengikuti bimbingan teknis atau memiliki sertifikat resmi, seluruh data sensitif (Nama KTP, NIK, WhatsApp, Email, dan file PDF sertifikat) akan dihapus permanen, dan catatan nomor sertifikat dipertahankan tanpa identitas ("Data pemegang telah dihapus") demi integritas profesi.</p>
                </div>
            </div>

            <div class="flex-shrink-0">
                <a href="{{ route('data-rights.portal', ['type' => 'hapus']) }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-colors flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Ajukan Penghapusan Akun
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
