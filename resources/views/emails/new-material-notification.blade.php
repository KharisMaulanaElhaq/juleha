<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Materi Pembelajaran Baru Tersedia</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 28px;">
        <h2 style="color: #1f1933; margin-top: 0;">Materi Pembelajaran Baru Tersedia</h2>
        <p>Halo <strong>{{ $participant->first_name ?? $participant->name }}</strong>,</p>
        <p>Panitia DPD JULEHA Kabupaten Brebes telah menerbitkan materi pembelajaran baru untuk kegiatan yang Anda ikuti:</p>
        
        <div style="background: #f1eff8; padding: 18px; border-radius: 6px; margin: 18px 0; border-left: 4px solid #5a3d9e;">
            <p style="margin: 4px 0;"><strong>Kegiatan:</strong> {{ $event->title ?? '-' }}</p>
            <p style="margin: 4px 0;"><strong>Judul Materi:</strong> <span style="font-weight: bold; color: #5a3d9e;">{{ $material->title }}</span></p>
            <p style="margin: 4px 0;"><strong>Tipe Materi:</strong> {{ $material->kind === 'file' ? 'Berkas Dokumen (' . strtoupper($material->extension) . ')' : 'Tautan Pembelajaran / Video' }}</p>
            @if(filled($material->description))
                <p style="margin: 8px 0 4px 0; font-size: 14px; color: #5f5877;"><strong>Keterangan:</strong> {{ $material->description }}</p>
            @endif
        </div>

        <p>Anda dapat mengakses, membaca, serta mengunduh materi ini secara resmi melalui Portal Peserta:</p>
        <div style="margin-top: 24px;">
            <a href="{{ route('participant.event.materials', $event->slug) }}" style="background: #5a3d9e; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">
                Buka & Unduh Materi Kegiatan
            </a>
        </div>

        <p style="margin-top: 28px; font-size: 13px; color: #64748b; line-height: 1.5;">
            Harap tidak membagikan tautan berkas privat ini kepada pihak di luar kelas pelatihan resmi. Keberkahan ilmu dan amanah keanggotaan DPD JULEHA Brebes senantiasa kami junjung tinggi.
        </p>
    </div>
</body>
</html>
