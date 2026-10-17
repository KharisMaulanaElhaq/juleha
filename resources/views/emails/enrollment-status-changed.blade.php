<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pemberitahuan Status Pendaftaran</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 28px;">
        <h2 style="color: #33204f; margin-top: 0;">Pembaruan Status Pendaftaran</h2>
        <p>Halo, <strong>{{ $user->name }}</strong>,</p>
        <p>Status pendaftaran Anda untuk kegiatan berikut telah diperbarui oleh panitia DPD JULEHA Brebes:</p>
        <div style="background: #f1f5f9; padding: 16px; border-radius: 6px; margin: 16px 0;">
            <p style="margin: 4px 0;"><strong>Kegiatan:</strong> {{ $event->title ?? '-' }}</p>
            <p style="margin: 4px 0;"><strong>Status Terkini:</strong> <span style="font-weight: bold; color: #7248b8;">{{ $statusLabel }}</span></p>
            @if(filled($adminNotes))
                <p style="margin: 4px 0;"><strong>Catatan Panitia:</strong> {{ $adminNotes }}</p>
            @endif
            @if($event && $event->starts_at)
                <p style="margin: 4px 0;"><strong>Jadwal Pelaksanaan:</strong> {{ $event->starts_at->translatedFormat('d F Y, H:i') }} WIB</p>
                <p style="margin: 4px 0;"><strong>Lokasi:</strong> {{ $event->location }}</p>
            @endif
        </div>
        <p>Anda dapat melihat rincian dan perkembangan kegiatan langsung melalui Portal Peserta:</p>
        <div style="margin-top: 24px;">
            <a href="{{ url('/dashboard') }}" style="background: #7248b8; color: #ffffff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">
                Buka Dashboard Peserta
            </a>
        </div>
        <p style="margin-top: 24px; font-size: 13px; color: #64748b;">
            Jika ada pertanyaan, silakan hubungi sekretariat DPD JULEHA Brebes melalui narahubung resmi.
        </p>
    </div>
</body>
</html>
