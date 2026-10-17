<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNik implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Validasi NIK Indonesia:
     * - Tepat 16 digit angka
     * - Tidak diawali angka 0
     * - Struktur tanggal lahir valid:
     *   - Digit 7-8: Tanggal lahir (pria 01-31, wanita 41-71 [tanggal + 40])
     *   - Digit 9-10: Bulan lahir (01-12)
     *   - Sisa digit: Provinsi (1-2), Kab/Kota (3-4), Kecamatan (5-6), Nomor urut (13-16)
     * - Catatan: Hanya memvalidasi struktur format matematis, tidak mengklaim verifikasi ke server Dukcapil.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nik = trim((string) $value);

        if (! preg_match('/^[1-9][0-9]{15}$/', $nik)) {
            $fail('NIK harus tepat 16 digit angka dan tidak diawali angka 0.');

            return;
        }

        // Ekstraksi tanggal lahir (posisi 6 dan 7, 0-indexed)
        $rawDay = (int) substr($nik, 6, 2);
        $day = $rawDay > 40 ? $rawDay - 40 : $rawDay;

        if ($day < 1 || $day > 31) {
            $fail('Format NIK tidak valid: bagian tanggal lahir tidak sesuai struktur KTP yang sah.');

            return;
        }

        // Ekstraksi bulan lahir (posisi 8 dan 9, 0-indexed)
        $month = (int) substr($nik, 8, 2);

        if ($month < 1 || $month > 12) {
            $fail('Format NIK tidak valid: bagian bulan lahir tidak sesuai kalender yang sah.');

            return;
        }
    }
}
