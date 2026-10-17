<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\EventRegistrationState;
use App\Enums\EventType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'short_title',
        'slug',
        'type',
        'program_id',
        'starts_at',
        'ends_at',
        'registration_deadline',
        'location',
        'quota',
        'fee',
        'description',
        'curriculum',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_deadline' => 'datetime',
            'curriculum' => 'array',
            'quota' => 'integer',
            'fee' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
            if (empty($event->type)) {
                $event->type = EventType::Bimtek;
            }
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function effectiveEndsAt(): ?Carbon
    {
        if ($this->ends_at) {
            return $this->ends_at->copy()->timezone('Asia/Jakarta');
        }

        $latestSessionEnd = $this->sessions()->max('ends_at');
        if ($latestSessionEnd) {
            return Carbon::parse($latestSessionEnd, 'Asia/Jakarta');
        }

        if ($this->starts_at) {
            return $this->starts_at->copy()->timezone('Asia/Jakarta')->endOfDay();
        }

        return null;
    }

    /**
     * Hitung kursi terpakai = pendaftaran berstatus Menunggu, Aktif, dan Selesai (status Ditolak tidak dihitung).
     */
    public function usedSeats(): int
    {
        $enrollmentCount = $this->enrollments()
            ->whereIn('status', [
                EnrollmentStatus::Menunggu,
                EnrollmentStatus::Aktif,
                EnrollmentStatus::Selesai,
            ])
            ->count();

        $legacyCount = $this->registrations()
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->count();

        return $enrollmentCount + $legacyCount;
    }

    /**
     * Sisa kursi yang tersedia. Mengembalikan null jika kuota tanpa batas (unlimited).
     */
    public function seatsLeft(): ?int
    {
        if ($this->quota === null) {
            return null;
        }

        return max(0, $this->quota - $this->usedSeats());
    }

    /**
     * Cek apakah kuota sudah penuh.
     */
    public function isFull(): bool
    {
        if ($this->quota === null) {
            return false;
        }

        return $this->usedSeats() >= $this->quota;
    }

    /**
     * Status pendaftaran kegiatan terhitung (Single Source of Truth).
     * Urutan evaluasi: finished -> closed -> deadline_passed -> full -> open.
     */
    public function registrationState(): EventRegistrationState
    {
        // 1. Selesai (finished)
        if ($this->status === 'done') {
            return EventRegistrationState::Finished;
        }

        $effectiveEnd = $this->effectiveEndsAt();
        if ($effectiveEnd && Carbon::now('Asia/Jakarta')->greaterThan($effectiveEnd)) {
            return EventRegistrationState::Finished;
        }

        // 2. Ditutup (closed)
        if ($this->status !== 'open') {
            return EventRegistrationState::Closed;
        }

        // 3. Batas pendaftaran lewat (deadline_passed)
        if ($this->registration_deadline) {
            $deadline = $this->registration_deadline->copy()->timezone('Asia/Jakarta');
            if (Carbon::now('Asia/Jakarta')->greaterThan($deadline)) {
                return EventRegistrationState::DeadlinePassed;
            }
        }

        // 4. Kuota penuh (full)
        if ($this->isFull()) {
            return EventRegistrationState::Full;
        }

        // 5. Dibuka (open)
        return EventRegistrationState::Open;
    }

    public function canRegister(): bool
    {
        return $this->registrationState() === EventRegistrationState::Open;
    }

    public function registrationStateMessage(): string
    {
        return $this->registrationState()->rejectionMessage();
    }

    public function registrationStateLabel(): string
    {
        return $this->registrationState()->label();
    }

    public function getConfirmedRegistrationsCountAttribute(): int
    {
        return $this->usedSeats();
    }

    public function getRemainingQuotaAttribute(): int
    {
        return $this->seatsLeft() ?? 9999;
    }

    public function isOpenForRegistration(): bool
    {
        return $this->canRegister();
    }

    public function isFinished(): bool
    {
        return $this->registrationState() === EventRegistrationState::Finished;
    }

    /**
     * Scope untuk kegiatan yang belum selesai (masih berlangsung atau mendatang).
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNotFinished(Builder $query): Builder
    {
        $now = Carbon::now('Asia/Jakarta');

        return $query->where('status', '!=', 'done')
            ->where(function (Builder $q) use ($now) {
                $q->where(function (Builder $sub) use ($now) {
                    $sub->whereNotNull('ends_at')
                        ->where('ends_at', '>=', $now);
                })->orWhere(function (Builder $sub) use ($now) {
                    $sub->whereNull('ends_at')
                        ->where('starts_at', '>=', $now->copy()->startOfDay());
                });
            });
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function hasQuota(): bool
    {
        return ! $this->isFull();
    }

    public function remainingQuota(): int
    {
        return $this->seatsLeft() ?? 9999;
    }
}
