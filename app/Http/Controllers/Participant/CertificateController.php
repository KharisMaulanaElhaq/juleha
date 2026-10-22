<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Setting;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    /**
     * Tampilkan halaman Sertifikat Saya di portal peserta.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $certificates = $user->certificates()
            ->with(['event'])
            ->latest('created_at')
            ->get();

        $waNumber = Setting::get('whatsapp_number', '082324448886');

        return view('pages.participant.certificates', [
            'user' => $user,
            'certificates' => $certificates,
            'waNumber' => $waNumber,
        ]);
    }

    /**
     * Unduh berkas PDF sertifikat asli dari LSP.
     * Hanya pemilik sah, tidak dicabut, dan anti-cache.
     */
    public function download(Request $request, Certificate $certificate): StreamedResponse
    {
        $user = $request->user();

        if (! $user || $user->id !== $certificate->user_id) {
            abort(403, 'Anda tidak memiliki hak untuk mengunduh sertifikat ini.');
        }

        if (! $user->isParticipant()) {
            abort(403, 'Hanya akun peserta pemilik sertifikat yang dapat mengunduh.');
        }

        if ($certificate->isRevoked()) {
            abort(403, 'Sertifikat ini telah dicabut oleh pengurus dan tidak dapat diunduh.');
        }

        if (! $certificate->isCompetent()) {
            abort(403, 'Hasil asesmen sertifikasi belum dinyatakan kompeten.');
        }

        return CertificateService::downloadForParticipant($certificate, $user);
    }
}
