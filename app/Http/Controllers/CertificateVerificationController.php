<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CertificateVerificationController extends Controller
{
    public const NOT_FOUND_MESSAGE = 'Catatan sertifikat tidak ditemukan dalam basis data DPD JULEHA Kabupaten Brebes.';

    /**
     * Tampilkan halaman formulir pencarian verifikasi nomor sertifikat.
     * Pencarian via NOMOR sertifikat TIDAK menampilkan nama pemegang.
     */
    public function index(Request $request)
    {
        $queryNumber = trim((string) $request->query('q', ''));
        $certificate = null;
        $errorMessage = null;

        $ip = $request->ip() ?: 'unknown';
        $failKey = 'cert_verify_fails:'.$ip;
        $fails = (int) Cache::get($failKey, 0);

        if (! empty($queryNumber)) {
            // Terapkan penundaan atau throttle lebih ketat jika terjadi beberapa pencarian gagal beruntun
            if ($fails >= 8) {
                abort(429, 'Terlalu banyak percobaan pencarian yang gagal. Silakan coba lagi beberapa menit lagi.');
            }

            if ($fails >= 3) {
                usleep(300000); // 300ms penundaan untuk memitigasi enumerasi
            }

            // Cari hanya yang kompeten (belum_kompeten tidak pernah ditampilkan)
            $found = Certificate::where('certificate_number', $queryNumber)
                ->where('result', Certificate::RESULT_KOMPETEN)
                ->with(['user', 'event'])
                ->first();

            if ($found) {
                Cache::forget($failKey);
                $certificate = $found;
            } else {
                Cache::put($failKey, $fails + 1, now()->addMinutes(5));
                usleep(150000); // Penundaan kecil tambahan pada pencarian gagal
                $errorMessage = self::NOT_FOUND_MESSAGE;
            }
        }

        $issuerName = Setting::get('cert_issuer_name', 'LSP Pertanian RI');
        $issuerLicense = Setting::get('cert_issuer_license', '');

        return view('pages.certificate-verify', [
            'certificate' => $certificate,
            'isSearchByNumber' => true,
            'queryNumber' => $queryNumber,
            'errorMessage' => $errorMessage,
            'issuerName' => $issuerName,
            'issuerLicense' => $issuerLicense,
        ]);
    }

    /**
     * Tampilkan hasil verifikasi langsung berdasarkan public_code unik.
     * Menampilkan nama pemegang tersamar (atau keterangan data pemegang dihapus jika akun dianonimkan).
     * Jika sertifikat dicabut, nama pemegang dan alasan internal TIDAK ditampilkan.
     */
    public function show(string $public_code)
    {
        $certificate = Certificate::where('public_code', $public_code)
            ->where('result', Certificate::RESULT_KOMPETEN)
            ->with(['user', 'event'])
            ->first();

        $issuerName = Setting::get('cert_issuer_name', 'LSP Pertanian RI');
        $issuerLicense = Setting::get('cert_issuer_license', '');

        if (! $certificate) {
            return view('pages.certificate-verify', [
                'certificate' => null,
                'isSearchByNumber' => false,
                'queryNumber' => '',
                'errorMessage' => self::NOT_FOUND_MESSAGE,
                'issuerName' => $issuerName,
                'issuerLicense' => $issuerLicense,
            ]);
        }

        return view('pages.certificate-verify', [
            'certificate' => $certificate,
            'isSearchByNumber' => false,
            'queryNumber' => $certificate->certificate_number,
            'errorMessage' => null,
            'issuerName' => $issuerName,
            'issuerLicense' => $issuerLicense,
        ]);
    }
}
