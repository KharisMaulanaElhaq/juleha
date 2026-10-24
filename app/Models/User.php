<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'google_id',
    'avatar',
    'email_verified_at',
    'is_active',
    'suspended_at',
    'suspended_reason',
    'last_login_at',
    'last_login_method',
    'anonymized_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN_KEGIATAN = 'admin_kegiatan';

    public const ROLE_EDITOR_KONTEN = 'editor_konten';

    public const ROLE_PARTICIPANT = 'participant';

    // Aliases untuk kompatibilitas
    public const ROLE_EVENT_ADMIN = 'admin_kegiatan';

    public const ROLE_CONTENT_EDITOR = 'editor_konten';

    protected $attributes = [
        'role' => self::ROLE_PARTICIPANT,
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->isForceDeleting() && $user->certificates()->exists()) {
                throw new \LogicException('Akun yang memiliki riwayat sertifikat tidak boleh dihapus permanen. Gunakan fitur anonimisasi akun.');
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_active === false || $this->trashed()) {
            return false;
        }

        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN_KEGIATAN,
            'event_admin',
            self::ROLE_EDITOR_KONTEN,
            'content_editor',
        ], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminKegiatan(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN_KEGIATAN,
            'event_admin',
            self::ROLE_SUPER_ADMIN,
        ], true);
    }

    public function isEventAdmin(): bool
    {
        return $this->isAdminKegiatan();
    }

    public function isEditorKonten(): bool
    {
        return in_array($this->role, [
            self::ROLE_EDITOR_KONTEN,
            'content_editor',
            self::ROLE_SUPER_ADMIN,
        ], true);
    }

    public function isContentEditor(): bool
    {
        return $this->isEditorKonten();
    }

    public function isParticipant(): bool
    {
        return in_array($this->role, [self::ROLE_PARTICIPANT, 'peserta'], true);
    }

    public function isStaff(): bool
    {
        return ! $this->isParticipant();
    }

    public function isActive(): bool
    {
        return $this->is_active !== false && ! $this->trashed();
    }

    public function isSuspended(): bool
    {
        return $this->is_active === false || $this->suspended_at !== null;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    /**
     * Cek apakah pengguna memiliki riwayat di sistem (pendaftaran, kehadiran, atau sertifikat).
     */
    public function hasHistory(): bool
    {
        return $this->enrollments()->exists() || $this->attendances()->exists() || $this->certificates()->exists();
    }

    /**
     * Scope hanya akun peserta biasa.
     */
    public function scopeParticipants(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_PARTICIPANT, 'peserta']);
    }

    /**
     * Scope akun pengurus / staf (semua role selain participant).
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereNotIn('role', [self::ROLE_PARTICIPANT, 'peserta']);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ParticipantProfile::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'target_id')
            ->where('target_type', 'User')
            ->latest('created_at');
    }

    /**
     * Nama depan pengguna yang konsisten untuk sapaan dan header portal.
     */
    public function getFirstNameAttribute(): string
    {
        $rawName = $this->profile?->full_name_ktp ?: $this->name;
        $parts = preg_split('/\s+/', trim((string) $rawName));

        return $parts[0] ?? (string) $this->name;
    }

    /**
     * Nama tampilan ringkas untuk portal.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->first_name;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }
}
