<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Consent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivacyDataController extends Controller
{
    /**
     * Tampilkan halaman Privasi dan Data Saya pada portal peserta.
     */
    public function index(): View
    {
        $user = Auth::user();
        $profile = $user->profile;

        $purposes = [
            Consent::PURPOSE_ACCOUNT_AND_ACTIVITY,
            Consent::PURPOSE_NIK_CERTIFICATE,
            Consent::PURPOSE_SHARE_TO_LSP,
            Consent::PURPOSE_COMMUNICATION_ANNOUNCEMENTS,
            Consent::PURPOSE_PHOTO_DOCUMENTATION,
        ];

        $consentRecords = Consent::where('user_id', $user->id)->get()->keyBy('purpose');

        $consentList = [];
        foreach ($purposes as $purpose) {
            $record = $consentRecords->get($purpose);
            $details = Consent::purposeDetails($purpose);

            $consentList[] = [
                'purpose' => $purpose,
                'name' => $details['name'],
                'description' => $details['description'],
                'consequence_if_withdrawn' => $details['consequence_if_withdrawn'],
                'is_mandatory' => $details['is_mandatory'],
                'is_granted' => $record ? $record->isGranted() : false,
                'granted_at' => $record?->granted_at,
                'withdrawn_at' => $record?->withdrawn_at,
                'version' => $record?->document_version ?? '1.0',
            ];
        }

        return view('pages.participant.privacy-data', [
            'user' => $user,
            'profile' => $profile,
            'consents' => $consentList,
        ]);
    }

    /**
     * Berikan persetujuan untuk tujuan tertentu (satu klik).
     */
    public function grant(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $purpose = $request->validate([
            'purpose' => ['required', 'string', 'in:akun_dan_kegiatan,nik_sertifikat,bagikan_ke_lsp,komunikasi_pengumuman,foto_dokumentasi'],
        ])['purpose'];

        Consent::grantPurpose($user->id, $purpose, '1.0');

        // Cek jika seluruh tujuan wajib terpenuhi, aktifkan flag profile consent
        $allMandatoryGranted = true;
        foreach (Consent::MANDATORY_PURPOSES as $mandatory) {
            if (! Consent::hasGranted($user->id, $mandatory)) {
                $allMandatoryGranted = false;
                break;
            }
        }

        if ($allMandatoryGranted && $user->profile) {
            $user->profile->update([
                'consent' => true,
                'consent_at' => now(),
            ]);
        }

        AuditLog::logUserActivity('consent_granted', $user->id, ['purpose' => $purpose]);

        $details = Consent::purposeDetails($purpose);

        return back()->with('success', "Persetujuan untuk \"{$details['name']}\" berhasil diaktifkan.");
    }

    /**
     * Tarik persetujuan untuk tujuan tertentu (satu klik).
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $purpose = $request->validate([
            'purpose' => ['required', 'string', 'in:akun_dan_kegiatan,nik_sertifikat,bagikan_ke_lsp,komunikasi_pengumuman,foto_dokumentasi'],
        ])['purpose'];

        Consent::withdrawPurpose($user->id, $purpose);

        // Jika tujuan wajib ditarik, nonaktifkan flag consent pada profil
        if (in_array($purpose, Consent::MANDATORY_PURPOSES, true) && $user->profile) {
            $user->profile->update([
                'consent' => false,
            ]);
        }

        AuditLog::logUserActivity('consent_withdrawn', $user->id, ['purpose' => $purpose]);

        $details = Consent::purposeDetails($purpose);

        return back()->with('warning', "Persetujuan untuk \"{$details['name']}\" telah ditarik. {$details['consequence_if_withdrawn']}");
    }

    /**
     * Unduh salinan data pribadi saya dalam format JSON (NIK tersamar).
     */
    public function downloadJson(): StreamedResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        $enrollments = $user->enrollments()->with('event')->get()->map(function ($e) {
            return [
                'kode_pendaftaran' => $e->id,
                'kegiatan' => $e->event?->title ?? '-',
                'tipe_kegiatan' => $e->event?->type?->value ?? '-',
                'status' => $e->status?->value ?? '-',
                'terdaftar_pada' => $e->created_at?->toIso8601String(),
            ];
        });

        $attendances = $user->attendances()->with('session.event')->get()->map(function ($a) {
            return [
                'sesi' => $a->session?->title ?? '-',
                'kegiatan' => $a->session?->event?->title ?? '-',
                'waktu_kehadiran' => $a->attended_at?->toIso8601String() ?? $a->created_at?->toIso8601String(),
            ];
        });

        $certificates = $user->certificates()->get()->map(function ($c) {
            return [
                'nomor_sertifikat' => $c->certificate_number,
                'skema' => $c->scheme_name,
                'lembaga_penerbit' => $c->issuer_name,
                'tanggal_terbit' => $c->issued_at?->toDateString(),
                'masa_berlaku_hingga' => $c->expires_at?->toDateString(),
                'status' => $c->status,
                'kode_verifikasi' => $c->public_code,
            ];
        });

        $consents = Consent::where('user_id', $user->id)->get()->map(function ($c) {
            return [
                'tujuan' => $c->purpose,
                'status' => $c->isGranted() ? 'diberikan' : 'ditarik',
                'versi_dokumen' => $c->document_version,
                'diberikan_pada' => $c->granted_at?->toIso8601String(),
                'ditarik_pada' => $c->withdrawn_at?->toIso8601String(),
            ];
        });

        $exportData = [
            'metadata' => [
                'pengendali_data' => setting('site_name', 'DPD JULEHA Kabupaten Brebes'),
                'tanggal_ekspor' => now()->toIso8601String(),
                'kepatuhan' => 'UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP)',
            ],
            'akun' => [
                'id_pengguna' => $user->id,
                'nama_akun' => $user->name,
                'email' => $user->email,
                'metode_masuk' => filled($user->google_id) ? 'Google OAuth' : 'Email/Password',
                'terdaftar_pada' => $user->created_at?->toIso8601String(),
            ],
            'profil_peserta' => [
                'nama_lengkap_ktp' => $profile?->full_name_ktp ?? $user->name,
                'nik_tersamar' => $profile?->masked_nik ?? 'Belum dilengkapi',
                'nomor_whatsapp' => $profile?->phone ?? '-',
                'kecamatan' => $profile?->district ?? '-',
                'desa' => $profile?->village ?? '-',
                'instansi_masjid' => $profile?->institution ?? '-',
                'status_keanggotaan' => $profile?->status ?? '-',
            ],
            'persetujuan_data' => $consents,
            'riwayat_kegiatan' => [
                'pendaftaran' => $enrollments,
                'kehadiran' => $attendances,
                'sertifikat' => $certificates,
            ],
        ];

        AuditLog::logUserActivity('personal_data_downloaded', $user->id);

        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = 'salinan-data-pribadi-juleha-user-'.$user->id.'.json';

        return response()->streamDownload(function () use ($jsonContent) {
            echo $jsonContent;
        }, $fileName, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
