<?php

namespace App\Services;

use App\Models\EventSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AttendanceTokenService
{
    public const TOKEN_TTL_SECONDS = 60; // Masa berlaku token (toleransi jaringan)

    public const REFRESH_INTERVAL_SECONDS = 45; // Interval ganti QR di layar proyektor

    /**
     * Membuat token dinamis dan kode angka 6 digit baru untuk sesi presensi tertentu.
     */
    public static function generateToken(EventSession $session): array
    {
        $token = Str::random(40);
        $numericCode = sprintf('%06d', random_int(100000, 999999));
        $expiresAt = Carbon::now('Asia/Jakarta')->addSeconds(self::TOKEN_TTL_SECONDS);

        $payload = [
            'session_id' => $session->id,
            'event_id' => $session->event_id,
            'token' => $token,
            'numeric_code' => $numericCode,
            'created_at' => Carbon::now('Asia/Jakarta')->timestamp,
            'expires_at' => $expiresAt->timestamp,
        ];

        // Simpan ke Cache dengan sedikit buffer waktu
        Cache::put("attendance_token_{$token}", $payload, self::TOKEN_TTL_SECONDS + 30);
        Cache::put("attendance_code_{$numericCode}", $payload, self::TOKEN_TTL_SECONDS + 30);
        Cache::put("session_active_token_{$session->id}", [
            'token' => $token,
            'numeric_code' => $numericCode,
            'payload' => $payload,
        ], self::TOKEN_TTL_SECONDS + 30);

        return [
            'token' => $token,
            'numeric_code' => $numericCode,
            'formatted_code' => substr($numericCode, 0, 3).' '.substr($numericCode, 3, 3),
            'url' => route('attendance.scan', ['token' => $token]),
            'refresh_in' => self::REFRESH_INTERVAL_SECONDS,
            'expires_in' => self::TOKEN_TTL_SECONDS,
            'session_id' => $session->id,
            'session_title' => $session->title,
            'event_title' => $session->event?->title ?? 'Kegiatan JULEHA Brebes',
            'location' => $session->location ?? ($session->event?->location ?? 'Brebes'),
            'hadir_count' => $session->hadir_count,
        ];
    }

    /**
     * Mengambil token aktif jika masih valid, atau generate baru jika sudah mendekati habis.
     */
    public static function getOrGenerateToken(EventSession $session): array
    {
        $active = Cache::get("session_active_token_{$session->id}");

        if ($active && isset($active['token'], $active['payload']['expires_at'])) {
            $secondsRemaining = $active['payload']['expires_at'] - Carbon::now('Asia/Jakarta')->timestamp;
            if ($secondsRemaining > 15) {
                $code = $active['numeric_code'] ?? ($active['payload']['numeric_code'] ?? '000000');

                return [
                    'token' => $active['token'],
                    'numeric_code' => $code,
                    'formatted_code' => substr($code, 0, 3).' '.substr($code, 3, 3),
                    'url' => route('attendance.scan', ['token' => $active['token']]),
                    'refresh_in' => min(self::REFRESH_INTERVAL_SECONDS, $secondsRemaining),
                    'expires_in' => $secondsRemaining,
                    'session_id' => $session->id,
                    'session_title' => $session->title,
                    'event_title' => $session->event?->title ?? 'Kegiatan JULEHA Brebes',
                    'location' => $session->location ?? ($session->event?->location ?? 'Brebes'),
                    'hadir_count' => $session->hadir_count,
                ];
            }
        }

        return self::generateToken($session);
    }

    /**
     * Memverifikasi token QR dan mengembalikan EventSession terkait jika valid.
     */
    public static function validateToken(string $token): ?EventSession
    {
        $payload = Cache::get("attendance_token_{$token}");

        if (! $payload || empty($payload['session_id'])) {
            return null;
        }

        $nowTimestamp = Carbon::now('Asia/Jakarta')->timestamp;
        if ($nowTimestamp > ($payload['expires_at'] ?? 0)) {
            return null;
        }

        return EventSession::with('event')->find($payload['session_id']);
    }

    /**
     * Memverifikasi kode angka 6 digit dan mengembalikan EventSession terkait jika valid.
     */
    public static function validateNumericCode(string $code): ?EventSession
    {
        $cleanCode = preg_replace('/[^\d]/', '', trim($code));

        if (strlen($cleanCode) !== 6) {
            return null;
        }

        $payload = Cache::get("attendance_code_{$cleanCode}");

        if (! $payload || empty($payload['session_id'])) {
            return null;
        }

        $nowTimestamp = Carbon::now('Asia/Jakarta')->timestamp;
        if ($nowTimestamp > ($payload['expires_at'] ?? 0)) {
            return null;
        }

        return EventSession::with('event')->find($payload['session_id']);
    }
}
