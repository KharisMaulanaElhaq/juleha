<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const RESULT_KOMPETEN = 'kompeten';

    public const RESULT_BELUM_KOMPETEN = 'belum_kompeten';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_SEGERA_BERAKHIR = 'segera_berakhir';

    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    public const STATUS_DICABUT = 'dicabut';

    public const STATUS_DATA_DIHAPUS = 'data_dihapus';

    public const STATUS_BELUM_KOMPETEN = 'belum_kompeten';

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'event_id',
        'result',
        'certificate_number',
        'scheme_name',
        'issuer_name',
        'issued_at',
        'valid_until',
        'file_path',
        'file_name',
        'file_size',
        'file_sha256',
        'public_code',
        'revoked_at',
        'revoked_reason',
        'internal_note',
        'reminders_sent',
        'holder_data_erased_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'valid_until' => 'date',
            'revoked_at' => 'datetime',
            'holder_data_erased_at' => 'datetime',
            'reminders_sent' => 'array',
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            if (empty($certificate->public_code)) {
                $certificate->public_code = self::generateUniquePublicCode();
            }

            if (empty($certificate->issuer_name)) {
                $certificate->issuer_name = Setting::get('cert_issuer_name', 'LSP Pertanian RI');
            }

            if (empty($certificate->valid_until) && $certificate->issued_at) {
                $years = (int) Setting::get('cert_validity_years', 3);
                $certificate->valid_until = $certificate->issued_at->copy()->addYears($years);
            }

            if (auth()->check() && empty($certificate->created_by)) {
                $certificate->created_by = auth()->id();
            }
        });

        static::updating(function (Certificate $certificate) {
            if (auth()->check()) {
                $certificate->updated_by = auth()->id();
            }
        });
    }

    /**
     * Generate kode verifikasi publik 12 digit unik tidak berurutan.
     */
    public static function generateUniquePublicCode(): string
    {
        do {
            // Gunakan karakter alfanumerik kapital tanpa karakter ambigu
            $code = strtoupper(Str::random(12));
        } while (self::withTrashed()->where('public_code', $code)->exists());

        return $code;
    }

    /* --------------------------------------------------------------------------
     | Relasi Database
     | -------------------------------------------------------------------------- */

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    /* --------------------------------------------------------------------------
     | Logika Status Turunan (Dihitung runtime, tidak disimpan)
     | -------------------------------------------------------------------------- */

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isHolderDataErased(): bool
    {
        return $this->holder_data_erased_at !== null || ($this->user && $this->user->isAnonymized());
    }

    public function isCompetent(): bool
    {
        return $this->result === self::RESULT_KOMPETEN;
    }

    public function isExpired(): bool
    {
        if (! $this->isCompetent() || ! $this->valid_until) {
            return false;
        }

        return $this->valid_until->isPast() && ! $this->valid_until->isToday();
    }

    public function isExpiringSoon(): bool
    {
        if (! $this->isCompetent() || ! $this->valid_until || $this->isExpired() || $this->isRevoked()) {
            return false;
        }

        $days = (int) now()->startOfDay()->diffInDays($this->valid_until->endOfDay(), false);

        return $days >= 0 && $days <= 90;
    }

    public function isActive(): bool
    {
        return $this->isCompetent()
            && ! $this->isRevoked()
            && ! $this->isExpired()
            && ! $this->isExpiringSoon();
    }

    /**
     * Hitung status turunan masa berlaku saat ini.
     * Status masa berlaku dihitung murni dari tanggal valid_until, kompetensi, dan status pencabutan.
     * Tidak digantikan oleh status penghapusan data pemegang (UU PDP).
     */
    public function getComputedStatus(): string
    {
        if ($this->isRevoked()) {
            return self::STATUS_DICABUT;
        }

        if (! $this->isCompetent()) {
            return self::STATUS_BELUM_KOMPETEN;
        }

        if ($this->isExpired()) {
            return self::STATUS_KEDALUWARSA;
        }

        if ($this->isExpiringSoon()) {
            return self::STATUS_SEGERA_BERAKHIR;
        }

        return self::STATUS_AKTIF;
    }

    public function getComputedStatusAttribute(): string
    {
        return $this->getComputedStatus();
    }

    public function getHolderDataStatusLabelAttribute(): ?string
    {
        return $this->isHolderDataErased() ? 'Data pemegang telah dihapus' : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->getComputedStatus()) {
            self::STATUS_AKTIF => 'Aktif',
            self::STATUS_SEGERA_BERAKHIR => 'Segera Berakhir',
            self::STATUS_KEDALUWARSA => 'Kedaluwarsa',
            self::STATUS_DICABUT => 'Dicabut',
            self::STATUS_DATA_DIHAPUS => 'Data Pemegang Dihapus',
            self::STATUS_BELUM_KOMPETEN => 'Belum Kompeten',
        };
    }

    public function getStatusFilamentColorAttribute(): string
    {
        return match ($this->getComputedStatus()) {
            self::STATUS_AKTIF => 'success',
            self::STATUS_SEGERA_BERAKHIR => 'warning',
            self::STATUS_KEDALUWARSA => 'danger',
            self::STATUS_DICABUT => 'danger',
            self::STATUS_DATA_DIHAPUS => 'gray',
            self::STATUS_BELUM_KOMPETEN => 'gray',
        };
    }

    public function getStatusFilamentIconAttribute(): string
    {
        return match ($this->getComputedStatus()) {
            self::STATUS_AKTIF => 'heroicon-o-check-circle',
            self::STATUS_SEGERA_BERAKHIR => 'heroicon-o-clock',
            self::STATUS_KEDALUWARSA => 'heroicon-o-x-circle',
            self::STATUS_DICABUT => 'heroicon-o-no-symbol',
            self::STATUS_DATA_DIHAPUS => 'heroicon-o-user-minus',
            self::STATUS_BELUM_KOMPETEN => 'heroicon-o-minus-circle',
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->getComputedStatus()) {
            self::STATUS_AKTIF => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800',
            self::STATUS_SEGERA_BERAKHIR => 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-300 dark:border-amber-800',
            self::STATUS_KEDALUWARSA => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-300 dark:border-rose-800',
            self::STATUS_DICABUT => 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300 border border-red-300 dark:border-red-800',
            self::STATUS_DATA_DIHAPUS => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700',
            self::STATUS_BELUM_KOMPETEN => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700',
        };
    }

    /**
     * Nama pemilik disamarkan untuk tampilan verifikasi publik (Nama depan + inisial sisanya).
     * Contoh: "Ahmad Dahlan" -> "Ahmad D.", "Kharis Maulana Elhaq" -> "Kharis M. E."
     * Jika data akun telah dianonimkan (UU PDP), tampilkan "Data pemegang telah dihapus".
     */
    public function getMaskedHolderNameAttribute(): string
    {
        if ($this->isHolderDataErased()) {
            return 'Data pemegang telah dihapus';
        }

        $rawName = trim($this->user?->name ?? 'Peserta');

        $words = preg_split('/\s+/', $rawName);
        if (count($words) <= 1) {
            return $rawName;
        }

        $firstName = array_shift($words);
        $initials = array_map(fn ($w) => strtoupper(mb_substr($w, 0, 1)).'.', $words);

        return $firstName.' '.implode(' ', $initials);
    }

    /**
     * Nama file unduhan yang ramah bagi peserta.
     */
    public function getDownloadFileNameAttribute(): string
    {
        $scheme = Str::slug($this->scheme_name ?: 'sertifikat');
        $name = Str::slug($this->user?->name ?: 'peserta');

        return "Sertifikat-{$scheme}-{$name}.pdf";
    }

    /* --------------------------------------------------------------------------
     | Scopes
     | -------------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('result', self::RESULT_KOMPETEN)
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now()->toDateString());
            });
    }

    public function scopeExpiringSoon(Builder $query, int $days = 90): Builder
    {
        $today = now()->toDateString();
        $limit = now()->addDays($days)->toDateString();

        return $query->where('result', self::RESULT_KOMPETEN)
            ->whereNull('revoked_at')
            ->whereNotNull('valid_until')
            ->whereBetween('valid_until', [$today, $limit]);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('result', self::RESULT_KOMPETEN)
            ->whereNull('revoked_at')
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->toDateString());
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }
}
