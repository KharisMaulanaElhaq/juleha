<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Ambil data pengaturan situs dari cache.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Setting::allCached();
        }

        $value = Setting::get($key, $default);

        // Jika nilai masih berisi placeholder [ISI: ...], kembalikan nilai default atau null aman
        if (is_string($value) && str_starts_with(trim($value), '[ISI:')) {
            return $default;
        }

        return $value;
    }
}

if (! function_exists('setting_has')) {
    /**
     * Periksa apakah suatu pengaturan ada dan tidak kosong.
     */
    function setting_has(string $key): bool
    {
        $val = setting($key);

        return filled($val);
    }
}

if (! function_exists('normalize_wa_number')) {
    /**
     * Normalisasi nomor telepon ke format internasional wa.me murni digit (62xxxxxxxxxx).
     */
    function normalize_wa_number(?string $phone): string
    {
        if (blank($phone)) {
            return '';
        }

        // Jika terdapat 2 nomor terhubung tanda hubung, ambil nomor pertama
        if (str_contains($phone, '-')) {
            $parts = explode('-', $phone);
            $phone = $parts[0];
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        if (blank($digits)) {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62'.$digits;
    }
}

if (! function_exists('setting_wa_link')) {
    /**
     * Dapatkan link wa.me resmi untuk tombol konsultasi atau pendaftaran.
     */
    function setting_wa_link(?string $customText = null, string $key = 'whatsapp_number'): string
    {
        $raw = setting($key);
        $normalized = normalize_wa_number($raw);

        if (blank($normalized)) {
            return route('kontak');
        }

        $url = 'https://wa.me/'.$normalized;

        if (filled($customText)) {
            $url .= '?text='.urlencode($customText);
        }

        return $url;
    }
}

if (! function_exists('setting_wa_display')) {
    /**
     * Dapatkan nomor WhatsApp dalam format rapi yang ramah pengunjung (contoh: 0823-2444-8886).
     */
    function setting_wa_display(string $key = 'whatsapp_number', ?string $default = null): string
    {
        $phone = setting($key);

        if (blank($phone)) {
            return $default ?? '';
        }

        if (str_contains($phone, '-')) {
            $parts = explode('-', $phone);
            $phone = $parts[0];
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        if (blank($digits)) {
            return $default ?? '';
        }

        if (str_starts_with($digits, '628')) {
            $digits = '08'.substr($digits, 3);
        } elseif (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (! str_starts_with($digits, '0')) {
            $digits = '0'.$digits;
        }

        $len = strlen($digits);

        if ($len === 12) {
            // 0823-2444-8886
            return substr($digits, 0, 4).'-'.substr($digits, 4, 4).'-'.substr($digits, 8);
        }

        if ($len === 11) {
            // 0812-345-6789
            return substr($digits, 0, 4).'-'.substr($digits, 4, 3).'-'.substr($digits, 7);
        }

        if ($len === 10) {
            // 0816-333-601
            return substr($digits, 0, 4).'-'.substr($digits, 4, 3).'-'.substr($digits, 7);
        }

        if ($len >= 13) {
            return substr($digits, 0, 4).'-'.substr($digits, 4, 4).'-'.substr($digits, 8);
        }

        return $digits;
    }
}
