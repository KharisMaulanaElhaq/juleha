<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DataRequest extends Model
{
    use HasFactory;

    public const TYPE_ACCESS = 'akses';

    public const TYPE_CORRECTION = 'koreksi';

    public const TYPE_ERASURE = 'hapus';

    public const TYPE_WITHDRAW = 'tarik_persetujuan';

    public const TYPE_OBJECTION = 'keberatan';

    public const TYPE_PORTABILITY = 'portabilitas';

    public const STATUS_NEW = 'baru';

    public const STATUS_VERIFICATION = 'verifikasi';

    public const STATUS_PROCESSING = 'diproses';

    public const STATUS_COMPLETED = 'selesai';

    public const STATUS_REJECTED = 'ditolak';

    protected $fillable = [
        'user_id',
        'request_number',
        'name',
        'contact',
        'request_type',
        'description',
        'status',
        'due_at',
        'handled_by',
        'resolution_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DataRequest $request) {
            if (empty($request->request_number)) {
                $datePrefix = 'REQ-'.date('Ymd').'-';
                $request->request_number = $datePrefix.strtoupper(Str::random(5));
            }

            if (empty($request->due_at)) {
                $slaDays = (int) setting('privacy_request_sla_days', 3);
                if ($slaDays <= 0) {
                    $slaDays = 3;
                }
                $request->due_at = now()->addDays($slaDays);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_REJECTED], true)) {
            return false;
        }

        return $this->due_at !== null && $this->due_at->isPast();
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_ACCESS => 'Hak Akses (Melihat Data Saya)',
            self::TYPE_CORRECTION => 'Hak Koreksi (Perbaikan Data)',
            self::TYPE_ERASURE => 'Hak Penghapusan / Anonimisasi Akun',
            self::TYPE_WITHDRAW => 'Penarikan Persetujuan Pemrosesan',
            self::TYPE_OBJECTION => 'Hak Keberatan Pemrosesan Data',
            self::TYPE_PORTABILITY => 'Hak Portabilitas (Unduh Salinan Data)',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Baru Masuk',
            self::STATUS_VERIFICATION => 'Verifikasi Identitas',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->request_type] ?? $this->request_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
