<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pemberitahuan Masa Berlaku Sertifikat Kompetensi</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px;">
        
        <!-- Header -->
        <div style="border-bottom: 2px solid #7248b8; padding-bottom: 16px; margin-bottom: 24px;">
            <h2 style="color: #33204f; margin: 0; font-size: 20px;">DPD JULEHA KABUPATEN BREBES</h2>
            <p style="margin: 4px 0 0; color: #64748b; font-size: 13px;">Fasilitasi Sertifikasi Kompetensi Juru Sembelih Halal</p>
        </div>

        <p>Yth. <strong>{{ $user->name }}</strong>,</p>

        @if($stage === 'expired')
            <p>
                Melalui surat elektronik ini kami memberitahukan bahwa masa berlaku sertifikat kompetensi Anda telah <strong>berakhir (kedaluwarsa)</strong>:
            </p>
        @else
            <p>
                Melalui surat elektronik ini kami menginformasikan bahwa sertifikat kompetensi Anda akan segera berakhir dalam <strong>{{ $stage }} hari ke depan</strong>:
            </p>
        @endif

        <!-- Rincian Sertifikat -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px; margin: 20px 0;">
            <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px 0; color: #64748b; width: 140px;">Nama Skema:</td>
                    <td style="padding: 4px 0; font-weight: bold; color: #0f172a;">{{ $certificate->scheme_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #64748b;">Lembaga Penerbit:</td>
                    <td style="padding: 4px 0; font-weight: bold; color: #0f172a;">{{ $certificate->issuer_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #64748b;">Nomor Sertifikat:</td>
                    <td style="padding: 4px 0; font-family: monospace; font-weight: bold; color: #7248b8;">{{ $certificate->certificate_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #64748b;">Tanggal Terbit:</td>
                    <td style="padding: 4px 0;">{{ $certificate->issued_at ? $certificate->issued_at->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #64748b;">Masa Berlaku Sampai:</td>
                    <td style="padding: 4px 0; font-weight: bold; color: {{ $stage === 'expired' ? '#dc2626' : '#d97706' }};">
                        {{ $certificate->valid_until ? $certificate->valid_until->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
            </table>
        </div>

        <p style="font-size: 14px; color: #334155;">
            Untuk informasi mengenai prosedur perpanjangan sertifikat atau jadwal asesmen berikutnya, silakan menghubungi narahubung panitia DPD JULEHA Kabupaten Brebes:
        </p>

        <!-- Tombol Aksi WhatsApp -->
        @php
            $waMsg = urlencode("Halo Panitia DPD JULEHA Brebes, saya ingin berkonsultasi mengenai perpanjangan sertifikat kompetensi {$certificate->scheme_name} (Nomor: {$certificate->certificate_number}).");
        @endphp
        <div style="margin: 24px 0; text-align: center;">
            <a href="https://wa.me/{{ $waNumber }}?text={{ $waMsg }}"
               style="background: #16a34a; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; font-size: 14px;">
                Hubungi Panitia via WhatsApp
            </a>
            <div style="margin-top: 12px;">
                <a href="{{ url('/dashboard/sertifikat') }}"
                   style="color: #7248b8; font-size: 13px; text-decoration: underline;">
                    Lihat Dokumen di Portal Peserta &rarr;
                </a>
            </div>
        </div>

        <p style="margin-top: 28px; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 16px;">
            Pemberitahuan otomatis ini dikirimkan oleh sistem informasi resmi DPD JULEHA Kabupaten Brebes. Anda tidak perlu membalas email ini secara langsung.
        </p>
    </div>
</body>
</html>
