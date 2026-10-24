<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocument extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const SLUG_KEBIJAKAN_PRIVASI = 'kebijakan-privasi';

    public const SLUG_SYARAT_KETENTUAN = 'syarat-ketentuan';

    public const SLUG_PENYIMPANAN_PERANGKAT = 'penyimpanan-perangkat';

    public const SLUG_DISCLAIMER = 'disclaimer';

    public const SLUG_HAK_CIPTA = 'hak-cipta-dan-atribusi';

    protected $fillable = [
        'slug',
        'title',
        'version',
        'effective_date',
        'content',
        'status',
        'change_summary',
        'is_mandatory_consent',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'is_mandatory_consent' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(LegalDocumentVersion::class)->orderByDesc('id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public static function findPublished(string $slug): ?self
    {
        return self::where('slug', $slug)
            ->where('status', self::STATUS_PUBLISHED)
            ->first();
    }

    public function recordVersion(string $changeSummary, ?int $publishedBy = null): LegalDocumentVersion
    {
        return $this->versions()->create([
            'version' => $this->version,
            'effective_date' => $this->effective_date ?? now(),
            'content' => $this->content,
            'change_summary' => $changeSummary,
            'published_by' => $publishedBy,
            'published_at' => now(),
        ]);
    }
}
