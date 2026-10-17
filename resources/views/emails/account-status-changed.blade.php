<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Akun DPD JULEHA Brebes</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
    <div style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #4c1d95; padding: 20px 24px; text-align: center;">
            <h1 style="color: #ffffff; font-size: 18px; margin: 0; font-weight: 700;">DPD JULEHA KABUPATEN BREBES</h1>
            <p style="color: #e9d5ff; font-size: 12px; margin: 4px 0 0 0;">Dewan Pengurus Daerah Juru Sembelih Halal</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 15px; margin-top: 0;">Assalamu'alaikum Wr. Wb.,</p>
            <p style="font-size: 14px; line-height: 1.6;">Yth. <strong>{{ $user->name }}</strong>,</p>

            @if($isActive)
                <div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 14px 16px; border-radius: 6px; margin: 18px 0;">
                    <p style="margin: 0; font-size: 14px; color: #065f46; font-weight: 600;">Akun Anda Telah Aktif Kembali</p>
                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #047857; line-height: 1.5;">
                        Pengurus DPD JULEHA Kabupaten Brebes telah mengaktifkan kembali akun Anda. Anda kini dapat masuk kembali ke portal resmi untuk mengakses layanan dan informasi kegiatan.
                    </p>
                </div>
                <div style="text-align: center; margin: 24px 0;">
                    <a href="{{ route('login') }}" style="display: inline-block; background-color: #4c1d95; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 10px 24px; border-radius: 8px;">Masuk ke Portal</a>
                </div>
            @else
                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 14px 16px; border-radius: 6px; margin: 18px 0;">
                    <p style="margin: 0; font-size: 14px; color: #991b1b; font-weight: 600;">Status Akun Dinonaktifkan</p>
                    <p style="margin: 6px 0 0 0; font-size: 13px; color: #b91c1c; line-height: 1.5;">
                        Akun Anda saat ini dinonaktifkan oleh administrator. Bila Anda merasa ini merupakan kekeliruan atau ingin mendapatkan bantuan informasi, silakan menghubungi pengurus DPD JULEHA Kabupaten Brebes.
                    </p>
                </div>
            @endif

            <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-top: 24px; border-top: 1px solid #f1f5f9; pt: 16px;">
                Jika ada kendala, hubungi sekretariat kami melalui kanal resmi atau WhatsApp admin panitia.<br>
                Wassalamu'alaikum Wr. Wb.
            </p>
        </div>
        <div style="background-color: #f8fafc; padding: 14px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} DPD JULEHA Kabupaten Brebes. Semua Hak Dilindungi.
        </div>
    </div>
</body>
</html>
