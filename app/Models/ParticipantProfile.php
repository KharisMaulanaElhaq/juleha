<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantProfile extends Model
{
    use HasFactory;

    /**
     * Versi dokumen persetujuan data sertifikasi kompetensi terbaru.
     */
    public const CURRENT_CONSENT_VERSION = 'v2_sertifikasi_lsp';

    protected $fillable = [
        'user_id',
        'phone',
        'full_name_ktp',
        'nik',
        'nik_hash',
        'district',
        'village',
        'institution',
        'status',
        'consent',
        'consent_version',
        'consent_at',
    ];

    /**
     * Periksa apakah peserta telah menyetujui versi persetujuan data terbaru.
     */
    public function hasCurrentConsent(): bool
    {
        return (bool) $this->consent && $this->consent_version === self::CURRENT_CONSENT_VERSION;
    }

    /**
     * Kolom NIK dan NIK hash disembunyikan secara permanen dari serialisasi JSON/Array.
     */
    protected $hidden = [
        'nik',
        'nik_hash',
    ];

    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'consent' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ParticipantProfile $profile) {
            if ($profile->isDirty('nik') && filled($profile->nik)) {
                $profile->nik_hash = self::hashNik($profile->nik);
            }

            if ($profile->isDirty('phone') && filled($profile->phone)) {
                $profile->phone = self::normalizePhone($profile->phone);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tampilan NIK tersamar (4 digit awal dan 4 digit akhir).
     */
    public function getMaskedNikAttribute(): string
    {
        $raw = (string) $this->nik;

        if (strlen($raw) < 8) {
            return '****************';
        }

        return substr($raw, 0, 4).'********'.substr($raw, -4);
    }

    /**
     * Menghitung HMAC-SHA256 dari NIK dengan kunci terpisah (NIK_HASH_KEY).
     */
    public static function hashNik(string $nik): string
    {
        $key = config('app.nik_hash_key') ?: (config('app.key') ?: 'juleha-brebes-nik-key');

        return hash_hmac('sha256', trim($nik), $key);
    }

    /**
     * Normalisasi nomor telepon ke format internasional 62xxxxxxxxxx.
     */
    public static function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62'.$cleaned;
        }

        return $cleaned;
    }

    /**
     * Format tampilan nomor HP (08xx-xxxx-xxxx).
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = $this->phone;
        if (str_starts_with($phone, '62')) {
            $phone = '0'.substr($phone, 2);
        }

        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4).'-'.substr($phone, 4, 4).'-'.substr($phone, 8);
        }

        return $phone;
    }
}
