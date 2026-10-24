<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalDocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_document_id',
        'version',
        'effective_date',
        'content',
        'change_summary',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function recordVersion(LegalDocument $document, string $changeSummary, ?int $publishedBy = null): self
    {
        return $document->recordVersion($changeSummary, $publishedBy);
    }
}
