<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id')->withTrashed();
    }

    /**
     * Catat aksi ke tabel audit umum (dengan IP & User Agent).
     */
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $meta = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'meta' => $meta,
        ]);
    }

    /**
     * Catat aktivitas manajemen pengguna tanpa menyimpan IP atau User Agent demi kepatuhan privasi.
     */
    public static function logUserActivity(string $action, int $targetUserId, ?array $meta = null, ?int $actorId = null): self
    {
        return self::create([
            'user_id' => $actorId ?? auth()->id(),
            'action' => $action,
            'target_type' => 'User',
            'target_id' => $targetUserId,
            'ip_address' => null,
            'user_agent' => null,
            'meta' => $meta,
        ]);
    }
}
