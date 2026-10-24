<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    use HasFactory;

    public const PURPOSE_ACCOUNT_AND_ACTIVITY = 'akun_dan_kegiatan';

    public const PURPOSE_NIK_CERTIFICATE = 'nik_sertifikat';

    public const PURPOSE_SHARE_TO_LSP = 'bagikan_ke_lsp';

    public const PURPOSE_COMMUNICATION_ANNOUNCEMENTS = 'komunikasi_pengumuman';

    public const PURPOSE_PHOTO_DOCUMENTATION = 'foto_dokumentasi';

    public const MANDATORY_PURPOSES = [
        self::PURPOSE_ACCOUNT_AND_ACTIVITY,
        self::PURPOSE_NIK_CERTIFICATE,
        self::PURPOSE_SHARE_TO_LSP,
    ];

    public const OPTIONAL_PURPOSES = [
        self::PURPOSE_COMMUNICATION_ANNOUNCEMENTS,
        self::PURPOSE_PHOTO_DOCUMENTATION,
    ];

    protected $fillable = [
        'user_id',
        'purpose',
        'document_version',
        'granted_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isGranted(): bool
    {
        return $this->granted_at !== null && $this->withdrawn_at === null;
    }

    public function isWithdrawn(): bool
    {
        return $this->withdrawn_at !== null;
    }

    public function isMandatory(): bool
    {
        return in_array($this->purpose, self::MANDATORY_PURPOSES, true);
    }

    /**
     * Berikan persetujuan untuk tujuan tertentu.
     */
    public static function grantPurpose(int $userId, string $purpose, ?string $version = null): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'purpose' => $purpose],
            [
                'document_version' => $version,
                'granted_at' => now(),
                'withdrawn_at' => null,
            ]
        );
    }

    /**
     * Tarik persetujuan untuk tujuan tertentu.
     */
    public static function withdrawPurpose(int $userId, string $purpose): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'purpose' => $purpose],
            [
                'withdrawn_at' => now(),
            ]
        );
    }

    /**
     * Periksa apakah user telah memberikan persetujuan aktif untuk tujuan tertentu.
     */
    public static function hasGranted(int $userId, string $purpose): bool
    {
        $consent = self::where('user_id', $userId)
            ->where('purpose', $purpose)
            ->first();

        if ($consent !== null) {
            return $consent->isGranted();
        }

        // Backward compatibility: jika record consent belum ada tapi profile punya consent=true
        $profile = ParticipantProfile::where('user_id', $userId)->first();
        if ($profile && (bool) $profile->consent) {
            // Daftarkan sebagai persetujuan awal
            self::grantPurpose($userId, $purpose, $profile->consent_version ?? '1.0');

            return true;
        }

        return false;
    }

    /**
     * Label dan deskripsi bahasa Indonesia awam per tujuan.
     */
    public static function purposeDetails(string $purpose): array
    {
        return match ($purpose) {
            self::PURPOSE_ACCOUNT_AND_ACTIVITY => [
                'name' => 'Akun dan Penyelenggaraan Pelatihan',
                'description' => 'Diperlukan untuk membuat akun peserta, memproses pendaftaran pelatihan, dan mencatat riwayat kehadiran Anda.',
                'consequence_if_withdrawn' => 'Akun tidak dapat digunakan untuk mendaftar atau mengikuti kegiatan pelatihan DPD JULEHA Brebes.',
                'is_mandatory' => true,
            ],
            self::PURPOSE_NIK_CERTIFICATE => [
                'name' => 'Validasi NIK untuk Pencetakan Sertifikat',
                'description' => 'Diperlukan untuk memvalidasi identitas 16 digit NIK Anda agar sertifikat bimbingan teknis dan kompetensi sah sesuai identitas resmi KTP.',
                'consequence_if_withdrawn' => 'Sertifikat pelatihan tidak dapat diterbitkan atau diverifikasi keabsahannya.',
                'is_mandatory' => true,
            ],
            self::PURPOSE_SHARE_TO_LSP => [
                'name' => 'Penerusan Data ke Lembaga Sertifikasi Profesi',
                'description' => 'Diperlukan untuk meneruskan nama sesuai KTP dan NIK Anda ke lembaga sertifikasi resmi mitra DPD (LSP) guna pendaftaran uji kompetensi nasional.',
                'consequence_if_withdrawn' => 'Anda tidak dapat diikutsertakan dalam uji kompetensi dan sertifikasi berstandar BNSP.',
                'is_mandatory' => true,
            ],
            self::PURPOSE_COMMUNICATION_ANNOUNCEMENTS => [
                'name' => 'Pemberitahuan Informasi dan Pengumuman (Opsional)',
                'description' => 'Kami dapat mengirimkan pengumuman jadwal kegiatan baru, materi tambahan, atau pembaruan melalui WhatsApp atau email terdaftar.',
                'consequence_if_withdrawn' => 'Anda tidak akan menerima notifikasi pengumuman baru, namun tetap dapat mengikuti pelatihan yang sudah terdaftar.',
                'is_mandatory' => false,
            ],
            self::PURPOSE_PHOTO_DOCUMENTATION => [
                'name' => 'Publikasi Dokumentasi Kegiatan (Opsional)',
                'description' => 'Foto atau rekaman suasana kegiatan pelatihan dapat ditampilkan secara wajar pada galeri situs dan media informasi DPD JULEHA Brebes.',
                'consequence_if_withdrawn' => 'Foto atau dokumentasi Anda tidak akan dipublikasikan pada materi promosi atau galeri situs web.',
                'is_mandatory' => false,
            ],
            default => [
                'name' => $purpose,
                'description' => '',
                'consequence_if_withdrawn' => '',
                'is_mandatory' => false,
            ],
        };
    }
}
