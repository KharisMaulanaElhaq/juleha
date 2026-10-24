<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Permohonan Hak Data DPD JULEHA Brebes</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
    <div style="max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background-color: #4c1d95; padding: 20px 24px; text-align: center;">
            <h1 style="color: #ffffff; font-size: 18px; margin: 0; font-weight: 700;">DPD JULEHA KABUPATEN BREBES</h1>
            <p style="color: #e9d5ff; font-size: 12px; margin: 4px 0 0 0;">Layanan Pelindungan Data Pribadi (UU PDP)</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 15px; margin-top: 0;">Assalamu'alaikum Wr. Wb.,</p>
            <p style="font-size: 14px; line-height: 1.6;">Yth. <strong>{{ $request->name }}</strong>,</p>

            <div style="background-color: #f5f3ff; border-left: 4px solid #7c3aed; padding: 14px 16px; border-radius: 6px; margin: 18px 0;">
                <p style="margin: 0; font-size: 14px; color: #5b21b6; font-weight: 700;">
                    Permohonan Hak Data Anda Telah Diterima
                </p>
                <p style="margin: 6px 0 0 0; font-size: 13px; color: #6d28d9; line-height: 1.5;">
                    Nomor Registrasi: <strong>{{ $request->request_number }}</strong><br>
                    Jenis Permohonan: <strong>{{ $request->type_label }}</strong><br>
                    Perkiraan Waktu Penyelesaian: <strong>{{ $request->due_at?->format('d M Y') ?? 'Sesuai SLA' }}</strong>
                </p>
            </div>

            <p style="font-size: 13px; line-height: 1.6; color: #334155;">
                Petugas privasi DPD JULEHA Brebes akan segera meneliti dan memverifikasi permohonan Anda. Demi keamanan, petugas dapat melakukan verifikasi identitas melalui kontak terdaftar sebelum data diserahkan atau tindakan dilaksanakan.
            </p>

            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin: 16px 0; font-size: 13px;">
                <strong style="color: #475569;">Uraian Permohonan Anda:</strong>
                <p style="margin: 4px 0 0 0; color: #1e293b; font-style: italic;">"{{ $request->description }}"</p>
            </div>

            <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                Pertanyaan terkait permohonan ini dapat disampaikan ke Petugas Privasi / Sekretariat DPD JULEHA Brebes di <strong>{{ $contactEmail }}</strong> dengan menyebutkan nomor registrasi di atas.<br><br>
                Wassalamu'alaikum Wr. Wb.
            </p>
        </div>
        <div style="background-color: #f8fafc; padding: 14px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} DPD JULEHA Kabupaten Brebes &bull; Layanan Hak Subjek Data
        </div>
    </div>
</body>
</html>
