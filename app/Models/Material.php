<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'kind',
        'path_or_url',
        'file_path',
        'external_url',
        'size',
        'sort_order',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Material $material) {
            if ($material->is_published && empty($material->published_at)) {
                $material->published_at = now();
            }

            if ($material->kind === 'file' && ! empty($material->path_or_url)) {
                if (Storage::disk('private')->exists($material->path_or_url)) {
                    $material->size = Storage::disk('private')->size($material->path_or_url);
                }
            } elseif ($material->kind === 'link') {
                $material->size = null;
            }
        });

        static::deleted(function (Material $material) {
            if ($material->kind === 'file' && ! empty($material->path_or_url)) {
                if (Storage::disk('private')->exists($material->path_or_url)) {
                    Storage::disk('private')->delete($material->path_or_url);
                }
            }
        });
    }

    public function getFilePathAttribute(): ?string
    {
        return $this->kind === 'file' ? $this->path_or_url : null;
    }

    public function setFilePathAttribute($value): void
    {
        if (! empty($value)) {
            $val = is_array($value) ? (reset($value) ?: null) : $value;
            if (! empty($val)) {
                $this->attributes['path_or_url'] = $val;
            }
        }
    }

    public function getExternalUrlAttribute(): ?string
    {
        return $this->kind === 'link' ? $this->path_or_url : null;
    }

    public function setExternalUrlAttribute($value): void
    {
        if (! empty($value)) {
            $val = is_array($value) ? (reset($value) ?: null) : $value;
            if (! empty($val)) {
                $this->attributes['path_or_url'] = $val;
            }
        }
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(MaterialDownload::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }

    public function isFile(): bool
    {
        return $this->kind === 'file';
    }

    public function isLink(): bool
    {
        return $this->kind === 'link';
    }

    public function isPdf(): bool
    {
        if (! $this->isFile()) {
            return false;
        }

        return str_ends_with(strtolower((string) $this->path_or_url), '.pdf');
    }

    public function isVideo(): bool
    {
        if ($this->isFile()) {
            return str_ends_with(strtolower((string) $this->path_or_url), '.mp4');
        }

        $url = strtolower((string) $this->path_or_url);

        return str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be') || str_contains($url, 'drive.google.com');
    }

    public function getExtensionAttribute(): string
    {
        if (! $this->isFile()) {
            return 'link';
        }

        return strtolower(pathinfo((string) $this->path_or_url, PATHINFO_EXTENSION)) ?: 'file';
    }

    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size) {
            return '-';
        }

        if ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 1).' MB';
        }

        return number_format($this->size / 1024, 0).' KB';
    }
}
