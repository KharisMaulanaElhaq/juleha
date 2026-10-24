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
        $ipHash = sha1($ip);
        $failKey = 'cert_verify_fails:'.$ipHash;
        $blockKey = 'cert_verify_blocked:'.$ipHash;

        // Jika IP sedang dalam masa blokir sementara (15 menit)
        if (Cache::has($blockKey)) {
            abort(429, 'Terlalu banyak percobaan, coba lagi nanti.');
        }

        if (! empty($queryNumber)) {
            // Cari hanya yang kompeten (belum_kompeten tidak pernah ditampilkan)
            $found = Certificate::where('certificate_number', $queryNumber)
                ->where('result', Certificate::RESULT_KOMPETEN)
                ->with(['user', 'event'])
                ->first();

            if ($found) {
                // Pencarian valid tidak menambah hitungan gagal
                $certificate = $found;
            } else {
                // Hitung kegagalan: setelah 5 kegagalan dalam 10 menit, blokir 15 menit
                $fails = (int) Cache::get($failKey, 0) + 1;
                if ($fails >= 5) {
                    Cache::put($blockKey, true, now()->addMinutes(15));
                    Cache::forget($failKey);
                    abort(429, 'Terlalu banyak percobaan, coba lagi nanti.');
                }

                Cache::put($failKey, $fails, now()->addMinutes(10));
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
