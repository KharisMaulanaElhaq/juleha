<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Http\Request;

class CertificateVerificationController extends Controller
{
    public const NOT_FOUND_MESSAGE = 'Catatan sertifikat tidak ditemukan dalam basis data DPD JULEHA Kabupaten Brebes.';

    /**
     * Tampilkan halaman formulir pencarian verifikasi nomor sertifikat.
     */
    public function index(Request $request)
    {
        $queryNumber = trim((string) $request->query('q', ''));
        $certificate = null;
        $errorMessage = null;

        if (! empty($queryNumber)) {
            // Cari hanya yang kompeten (belum_kompeten tidak pernah ditampilkan)
            $found = Certificate::where('certificate_number', $queryNumber)
                ->where('result', Certificate::RESULT_KOMPETEN)
                ->with(['user', 'event'])
                ->first();

            if ($found) {
                // Alihkan ke URL publik berkode unik agar konsisten
                return redirect()->route('certificate.verify.code', $found->public_code);
            }

            // Pesan seragam untuk mencegah enumerasi
            $errorMessage = self::NOT_FOUND_MESSAGE;
        }

        $issuerName = Setting::get('cert_issuer_name', 'LSP Pertanian RI');
        $issuerLicense = Setting::get('cert_issuer_license', '');

        return view('pages.certificate-verify', [
            'certificate' => null,
            'queryNumber' => $queryNumber,
            'errorMessage' => $errorMessage,
            'issuerName' => $issuerName,
            'issuerLicense' => $issuerLicense,
        ]);
    }

    /**
     * Tampilkan hasil verifikasi langsung berdasarkan public_code unik.
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
                'queryNumber' => '',
                'errorMessage' => self::NOT_FOUND_MESSAGE,
                'issuerName' => $issuerName,
                'issuerLicense' => $issuerLicense,
            ]);
        }

        return view('pages.certificate-verify', [
            'certificate' => $certificate,
            'queryNumber' => $certificate->certificate_number,
            'errorMessage' => null,
            'issuerName' => $issuerName,
            'issuerLicense' => $issuerLicense,
        ]);
    }
}
