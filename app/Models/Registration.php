<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'code',
        'full_name',
        'phone',
        'email',
        'district',
        'village',
        'participant_type',
        'notes',
        'status',
        'consent',
        'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }

    public static function normalizePhone(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        // Strip non-digits
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62'.$digits;
    }

    public static function generateUniqueCode(): string
    {
        do {
            $yearMonth = date('ym');
            $random = strtoupper(Str::random(4));
            $code = "JLH-{$yearMonth}-{$random}";
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public static function participantTypeLabels(): array
    {
        return [
            'calon_juleha' => 'Calon Juru Sembelih Halal',
            'juleha_aktif' => 'Juru Sembelih Halal Aktif',
            'pengurus_masjid' => 'Pengurus DKM / Panitia Kurban Masjid',
            'pekerja_rph' => 'Petugas RPH / Jagal Tradisional',
            'lainnya' => 'Masyarakat Umum / Pemerhati Halal',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'rejected' => 'Ditolak',
            'attended' => 'Hadir',
        ];
    }

    public function getParticipantTypeLabelAttribute(): string
    {
        return static::participantTypeLabels()[$this->participant_type] ?? $this->participant_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }
}
