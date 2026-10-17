<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'starts_at',
        'ends_at',
        'location',
        'attendance_open',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'attendance_open' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Memeriksa apakah sesi presensi sedang dibuka secara manual atau sedang berlangsung sesuai jadwal waktu.
     */
    public function isOpenForAttendance(): bool
    {
        if ($this->attendance_open) {
            return true;
        }

        $now = Carbon::now('Asia/Jakarta');

        if (! $this->starts_at) {
            return false;
        }

        // Toleransi: 30 menit sebelum sesi dimulai hingga 30 menit setelah estimasi selesai
        $startWithTolerance = $this->starts_at->copy()->timezone('Asia/Jakarta')->subMinutes(30);
        $endWithTolerance = $this->ends_at
            ? $this->ends_at->copy()->timezone('Asia/Jakarta')->addMinutes(30)
            : $this->starts_at->copy()->timezone('Asia/Jakarta')->addHours(4);

        return $now->between($startWithTolerance, $endWithTolerance);
    }

    public function getHadirCountAttribute(): int
    {
        return $this->attendances()->where('status', 'hadir')->count();
    }

    public function getFormattedScheduleAttribute(): string
    {
        if (! $this->starts_at) {
            return '-';
        }

        $start = $this->starts_at->timezone('Asia/Jakarta');
        $end = $this->ends_at ? $this->ends_at->timezone('Asia/Jakarta') : null;

        return $start->translatedFormat('d M Y, H:i').($end ? ' – '.$end->format('H:i').' WIB' : ' WIB');
    }
}
