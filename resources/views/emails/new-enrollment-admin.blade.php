<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pendaftar Baru Kegiatan</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 28px;">
        <h2 style="color: #33204f; margin-top: 0;">Pemberitahuan Pendaftar Baru</h2>
        <p>Ada pendaftar baru yang masuk untuk kegiatan berikut:</p>
        <div style="background: #f1f5f9; padding: 16px; border-radius: 6px; margin: 16px 0;">
            <p style="margin: 4px 0;"><strong>Kegiatan:</strong> {{ $event->title ?? '-' }}</p>
            <p style="margin: 4px 0;"><strong>Nama Peserta:</strong> {{ $profile?->full_name_ktp ?? $user->name }}</p>
            <p style="margin: 4px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 4px 0;"><strong>WhatsApp:</strong> {{ $profile?->formatted_phone ?? $profile?->phone ?? '-' }}</p>
            <p style="margin: 4px 0;"><strong>Domisili:</strong> Kecamatan {{ $profile?->district ?? '-' }}, Desa {{ $profile?->village ?? '-' }}</p>
            <p style="margin: 4px 0;"><strong>Waktu Daftar:</strong> {{ $enrollment->created_at?->format('d M Y H:i') }} WIB</p>
        </div>
        <p>Silakan tinjau dan konfirmasi pendaftaran melalui Panel Admin Filament DPD JULEHA Brebes.</p>
        <div style="margin-top: 24px;">
            <a href="{{ url('/admin/enrollments') }}" style="background: #7248b8; color: #ffffff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">
                Buka Panel Admin
            </a>
        </div>
    </div>
</body>
</html>
