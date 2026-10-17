<?php

namespace App\Enums;

enum EventRegistrationState: string
{
    case Open = 'open';
    case Full = 'full';
    case Closed = 'closed';
    case DeadlinePassed = 'deadline_passed';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Dibuka',
            self::Full => 'Penuh',
            self::Closed => 'Ditutup',
            self::DeadlinePassed => 'Batas Lewat',
            self::Finished => 'Selesai',
        };
    }

    public function buttonLabel(): string
    {
        return match ($this) {
            self::Open => 'Daftar Sekarang',
            self::Full => 'Kuota penuh',
            self::Closed => 'Pendaftaran ditutup',
            self::DeadlinePassed => 'Batas pendaftaran terlewat',
            self::Finished => 'Kegiatan selesai',
        };
    }

    public function rejectionMessage(): string
    {
        return match ($this) {
            self::Open => 'Pendaftaran dibuka.',
            self::Full => 'Maaf, kuota kegiatan ini sudah penuh.',
            self::Closed => 'Pendaftaran kegiatan ini sudah ditutup.',
            self::DeadlinePassed => 'Batas pendaftaran sudah lewat.',
            self::Finished => 'Kegiatan ini sudah selesai.',
        };
    }

    public function lucideIcon(): string
    {
        return match ($this) {
            self::Open => 'clipboard-check',
            self::Full => 'users-round',
            self::Closed => 'lock',
            self::DeadlinePassed => 'clock-alert',
            self::Finished => 'check-circle-2',
        };
    }

    public function filamentIcon(): string
    {
        return match ($this) {
            self::Open => 'heroicon-m-check-circle',
            self::Full => 'heroicon-m-user-group',
            self::Closed => 'heroicon-m-lock-closed',
            self::DeadlinePassed => 'heroicon-m-clock',
            self::Finished => 'heroicon-m-check-badge',
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Full => 'warning',
            self::Closed => 'danger',
            self::DeadlinePassed => 'rose',
            self::Finished => 'gray',
        };
    }
}
