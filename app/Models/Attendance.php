<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_session_id',
        'user_id',
        'status',
        'source',
        'recorded_by',
        'attended_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'attended_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'event_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isHadir(): bool
    {
        return $this->status === AttendanceStatus::Hadir;
    }

    public function isManual(): bool
    {
        return $this->source === 'manual';
    }

    public function isQr(): bool
    {
        return $this->source === 'qr';
    }
}
