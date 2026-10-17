<?php

namespace App\Enums;

enum EventType: string
{
    case Bimtek = 'bimtek';
    case SertifikasiKompetensi = 'sertifikasi_kompetensi';
    case SertifikasiBnsp = 'sertifikasi_bnsp';
    case Tot = 'tot';

    public function label(): string
    {
        return match ($this) {
            self::Bimtek => 'Bimtek',
            self::SertifikasiKompetensi => 'Sertifikasi Kompetensi',
            self::SertifikasiBnsp => 'Sertifikasi BNSP',
            self::Tot => 'Training of Trainer (TOT)',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Bimtek => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700',
            self::SertifikasiKompetensi => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-300 dark:border-sky-700',
            self::SertifikasiBnsp => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-300 dark:border-purple-700',
            self::Tot => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Bimtek => 'success',
            self::SertifikasiKompetensi => 'info',
            self::SertifikasiBnsp => 'purple',
            self::Tot => 'warning',
        };
    }
}
