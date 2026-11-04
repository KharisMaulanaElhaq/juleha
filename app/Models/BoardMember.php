<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardMember extends Model
{
    use HasFactory;

    public const KIND_PERSON = 'person';

    public const KIND_INSTITUTION = 'institution';

    public const GROUP_PELINDUNG_PEMBINA = 'pelindung_pembina';

    public const GROUP_PIMPINAN_HARIAN = 'pimpinan_harian';

    public const GROUP_BIDANG = 'bidang';

    protected $fillable = [
        'kind',
        'group',
        'division',
        'name',
        'position',
        'category',
        'photo',
        'photo_consent_at',
        'photo_consent_note',
        'photo_version',
        'bio',
        'sort_order',
        'is_active',
        'internal_note',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'photo_consent_at' => 'date',
            'photo_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BoardMember $member) {
            if ($member->isInstitution()) {
                if ($member->photo) {
                    app(\App\Services\BoardMemberPhotoService::class)->deletePhotoFiles($member->photo);
                }
                $member->photo = null;
                $member->photo_consent_at = null;
                $member->photo_consent_note = null;
            }
        });

        static::updating(function (BoardMember $member) {
            if ($member->isDirty('photo')) {
                $originalPhoto = $member->getOriginal('photo');
                if (! empty($originalPhoto) && $originalPhoto !== $member->photo) {
                    app(\App\Services\BoardMemberPhotoService::class)->deletePhotoFiles($originalPhoto);
                }
            }
        });

        static::deleting(function (BoardMember $member) {
            if ($member->photo) {
                app(\App\Services\BoardMemberPhotoService::class)->deletePhotoFiles($member->photo);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopePelindungPembina($query)
    {
        return $query->where('group', self::GROUP_PELINDUNG_PEMBINA)->orderBy('sort_order');
    }

    public function scopePimpinanHarian($query)
    {
        return $query->where('group', self::GROUP_PIMPINAN_HARIAN)->orderBy('sort_order');
    }

    public function scopeBidang($query)
    {
        return $query->where('group', self::GROUP_BIDANG)->orderBy('sort_order');
    }

    public function getResolvedPhotoPath(): ?string
    {
        if (empty($this->photo)) {
            return null;
        }

        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

        if ($publicDisk->exists($this->photo)) {
            return $this->photo;
        }

        if ($publicDisk->exists('board-members/main/' . $this->photo)) {
            return 'board-members/main/' . $this->photo;
        }

        if ($publicDisk->exists('board-members/' . $this->photo)) {
            return 'board-members/' . $this->photo;
        }

        return null;
    }

    public function photoExists(): bool
    {
        return $this->getResolvedPhotoPath() !== null;
    }

    public function isPhotoMissing(): bool
    {
        return ! empty($this->photo) && ! $this->photoExists();
    }

    public function hasPhoto(): bool
    {
        return ! empty($this->photo) && $this->photoExists();
    }

    public function getThumbPathAttribute(): ?string
    {
        $resolved = $this->getResolvedPhotoPath();
        if (! $resolved) {
            return null;
        }

        return app(\App\Services\BoardMemberPhotoService::class)->getThumbPath($resolved);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $resolved = $this->getResolvedPhotoPath();
        if (! $resolved) {
            return null;
        }

        $version = $this->photo_version ? '?v=' . $this->photo_version : '';

        return asset('storage/' . $resolved) . $version;
    }

    public function getThumbUrlAttribute(): ?string
    {
        $resolved = $this->getResolvedPhotoPath();
        if (! $resolved) {
            return null;
        }

        $thumb = $this->thumb_path;
        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
        $actualPath = ($thumb && $publicDisk->exists($thumb)) ? $thumb : $resolved;
        $version = $this->photo_version ? '?v=' . $this->photo_version : '';

        return asset('storage/' . $actualPath) . $version;
    }

    public function getPhotoSrcsetAttribute(): ?string
    {
        if (! $this->hasPhoto()) {
            return null;
        }

        return "{$this->thumb_url} 400w, {$this->photo_url} 800w";
    }

    public function isInstitution(): bool
    {
        return $this->kind === self::KIND_INSTITUTION;
    }

    public function isPerson(): bool
    {
        return $this->kind === self::KIND_PERSON;
    }

    /**
     * Dapatkan nama bersih tanpa gelar depan/belakang untuk avatar inisial.
     */
    public function getCleanNameAttribute(): string
    {
        // 1. Potong gelar di belakang koma (mis: "Ujang TSM, SH." -> "Ujang TSM")
        $base = explode(',', $this->name)[0];

        // 2. Bersihkan gelar depan seperti dr., drh., H., Hj., K.H., KH., Ustadz, Drs., Ir.
        $cleaned = preg_replace('/^(drh\.|dr\.|h\.|hj\.|k\.h\.|kh\.|ustadz|drs\.|ir\.)\s+/i', '', trim($base));

        return trim($cleaned);
    }

    /**
     * Inisial 1-2 huruf tanpa gelar akademis.
     */
    public function getInitialsAttribute(): string
    {
        $cleanName = $this->clean_name;
        $words = preg_split('/\s+/', $cleanName, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        if (count($words) === 1) {
            return strtoupper(mb_substr($words[0], 0, min(2, mb_strlen($words[0]))));
        }

        return 'JH';
    }
}
