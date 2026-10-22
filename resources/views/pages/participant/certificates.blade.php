@extends('layouts.portal', [
    'title' => 'Sertifikat Saya',
    'activeTab' => 'participant.certificates'
])

@section('content')
<div class="space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs portal-text-muted mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <span>&bull;</span>
                <span class="font-medium portal-text">Sertifikat Saya</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black portal-text flex items-center gap-2">
                <i data-lucide="award" class="w-6 h-6 text-amber-500"></i>
                <span>Sertifikat Saya</span>
            </h1>
            <p class="text-xs sm:text-sm portal-text-muted mt-0.5">
                Catatan hasil uji kompetensi dan berkas sertifikat digital resmi yang difasilitasi DPD JULEHA Kabupaten Brebes.
            </p>
        </div>

        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border portal-border text-xs font-semibold portal-text-muted hover:portal-text hover:bg-slate-100 dark:hover:bg-slate-800 transition self-start sm:self-auto">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Informasi Penting: Keabsahan & Peran Sistem -->
    <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
        <div class="text-xs text-amber-900 dark:text-amber-200 space-y-1 leading-relaxed">
            <p class="font-bold">Informasi Penerbitan Sertifikat Kompetensi BNSP</p>
            <p>
                DPD JULEHA Kabupaten Brebes memfasilitasi pelaksanaan uji kompetensi. Sertifikat digital diterbitkan secara resmi oleh Lembaga Sertifikasi Profesi (LSP). Sistem ini hanya menyimpan dan membagikan berkas asli dari LSP serta menyediakan verifikasi catatan DPD.
            </p>
        </div>
    </div>

    <!-- Daftar Sertifikat -->
    @if($certificates->isEmpty())
        <div class="portal-card p-8 sm:p-12 text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
                <i data-lucide="award" class="w-7 h-7"></i>
            </div>
            <h2 class="text-base font-bold portal-text">Belum Ada Catatan Sertifikat</h2>
            <p class="text-xs sm:text-sm portal-text-muted max-w-md mx-auto">
                Catatan sertifikat uji kompetensi akan tampil di halaman ini setelah asesmen selesai dan hasil dicatat oleh panitia sertifikasi.
            </p>
            <div class="pt-2">
                <a href="{{ route('dashboard') }}#kegiatan-saya"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl portal-btn-primary text-xs font-bold">
                    <span>Lihat Kegiatan Saya</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($certificates as $cert)
                @php
                    $isCompetent = $cert->isCompetent();
                    $isRevoked = $cert->isRevoked();
                    $isExpired = $cert->isExpired();
                    $isExpiringSoon = $cert->isExpiringSoon();
                    $status = $cert->computed_status;
                    $verifyUrl = url('/verifikasi/' . $cert->public_code);
                    $waHelpText = urlencode("Halo Panitia DPD JULEHA Brebes, saya ingin menanyakan perpanjangan sertifikat kompetensi {$cert->scheme_name} (Nomor: {$cert->certificate_number}).");
                    $waShareText = urlencode("Verifikasi Sertifikat Kompetensi BNSP saya dari {$cert->issuer_name}:\nSkema: {$cert->scheme_name}\nNomor: {$cert->certificate_number}\nTautan verifikasi DPD JULEHA Brebes:\n{$verifyUrl}");
                @endphp

                <div class="portal-card p-5 sm:p-6 space-y-5 border-t-2 {{ $isRevoked ? 'border-red-500' : ($isCompetent ? 'border-[var(--gold)]' : 'border-slate-400') }}">
                    
                    <!-- Header Kartu: Skema & Badge Status -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                                {{ $cert->issuer_name }}
                            </span>
                            <h2 class="text-lg sm:text-xl font-bold portal-text">
                                {{ $cert->scheme_name }}
                            </h2>
                            @if($cert->event)
                                <p class="text-xs portal-text-muted flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>{{ $cert->event->title }}</span>
                                </p>
                            @endif
                        </div>

                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $cert->status_badge_classes }}">
                                <i data-lucide="{{ $cert->status_filament_icon }}" class="w-3.5 h-3.5"></i>
                                <span>{{ $cert->status_label }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Rincian Nomor & Tanggal Masa Berlaku -->
                    @if($isCompetent)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border portal-border text-xs">
                            <div>
                                <span class="portal-text-muted block text-[11px]">Nomor Sertifikat</span>
                                <span class="font-bold portal-text font-mono text-xs sm:text-sm">{{ $cert->certificate_number ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="portal-text-muted block text-[11px]">Tanggal Terbit</span>
                                <span class="font-semibold portal-text">{{ $cert->issued_at ? $cert->issued_at->translatedFormat('d F Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="portal-text-muted block text-[11px]">Berlaku Sampai</span>
                                <span class="font-semibold portal-text">{{ $cert->valid_until ? $cert->valid_until->translatedFormat('d F Y') : '-' }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- PESAN KHUSUS STATUS TERTENTU -->
                    @if(! $isCompetent)
                        <!-- Kondisi Belum Kompeten: Netral & Sopan -->
                        <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-2">
                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-medium">
                                Belum kompeten pada asesmen ini. Hubungi panitia untuk informasi asesmen ulang.
                            </p>
                            @php
                                $waReassess = urlencode("Halo Panitia DPD JULEHA Brebes, saya ingin menanyakan jadwal atau prosedur asesmen ulang untuk kegiatan {$cert->event?->title}.");
                            @endphp
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $waNumber) }}?text={{ $waReassess }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg portal-btn-whatsapp text-xs font-semibold">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Hubungi Panitia via WhatsApp</span>
                            </a>
                        </div>
                    @elseif($isRevoked)
                        <!-- Kondisi Dicabut -->
                        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 space-y-1 text-xs text-red-800 dark:text-red-200">
                            <p class="font-bold">Sertifikat ini telah dicabut oleh pengurus DPD JULEHA Kabupaten Brebes.</p>
                            <p><span class="font-semibold">Alasan:</span> {{ $cert->revoked_reason ?? 'Tidak disebutkan.' }}</p>
                        </div>
                    @elseif($isExpiringSoon || $isExpired)
                        <!-- Kondisi Segera Berakhir atau Kedaluwarsa -->
                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 space-y-2">
                            <p class="text-xs sm:text-sm text-amber-900 dark:text-amber-200 font-medium">
                                Masa berlaku sertifikat kompetensi Anda {{ $isExpired ? 'telah berakhir' : 'segera berakhir (kurang dari 90 hari)' }}. Hubungi panitia untuk informasi perpanjangan.
                            </p>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $waNumber) }}?text={{ $waHelpText }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg portal-btn-whatsapp text-xs font-semibold">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Hubungi Panitia via WhatsApp</span>
                            </a>
                        </div>
                    @endif

                    <!-- TOMBOL AKSI (UNDUH, SALIN TAUTAN, BAGIKAN) -->
                    @if($isCompetent && ! $isRevoked)
                        <div class="pt-2 border-t portal-border flex flex-wrap items-center gap-2.5">
                            @if($cert->file_path)
                                <!-- Unduh Sertifikat PDF Asli LSP -->
                                <a href="{{ route('participant.certificates.download', $cert) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl portal-btn-primary text-xs font-bold shadow-xs">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    <span>Unduh sertifikat (PDF)</span>
                                </a>
                            @else
                                <div class="text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-3 py-2 rounded-xl border border-amber-200 dark:border-amber-900/40 flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4 shrink-0"></i>
                                    <span>Berkas digital resmi dari LSP sedang dalam proses verifikasi & pengunggahan panitia.</span>
                                </div>
                            @endif

                            <!-- Salin Tautan Verifikasi -->
                            <button type="button"
                                    onclick="copyToClipboard('{{ $verifyUrl }}', this)"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border portal-border text-xs font-semibold portal-text hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span class="btn-text">Salin tautan verifikasi</span>
                            </button>

                            <!-- Bagikan lewat WhatsApp -->
                            <a href="https://wa.me/?text={{ $waShareText }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border portal-border text-xs font-semibold text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition">
                                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                <span>Bagikan lewat WhatsApp</span>
                            </a>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

</div>

@push('scripts')
<script>
    function copyToClipboard(text, btnElement) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showSuccess).catch(fallback);
        } else {
            fallback();
        }

        function fallback() {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                showSuccess();
            } catch (err) {
                alert('Gagal menyalin tautan.');
            }
            document.body.removeChild(textArea);
        }

        function showSuccess() {
            const textSpan = btnElement.querySelector('.btn-text');
            const originalText = textSpan.innerText;
            textSpan.innerText = 'Tautan disalin!';
            btnElement.classList.add('border-emerald-500', 'text-emerald-600');
            setTimeout(() => {
                textSpan.innerText = originalText;
                btnElement.classList.remove('border-emerald-500', 'text-emerald-600');
            }, 2000);
        }
    }
</script>
@endpush
@endsection
