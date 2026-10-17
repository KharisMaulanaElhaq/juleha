<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Menunggu = 'menunggu';
    case Aktif = 'aktif';
    case Ditolak = 'ditolak';
    case Selesai = 'selesai';

    // Alias kompatibilitas
    public const MenungguVerifikasi = self::Menunggu;

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu diperiksa',
            self::Aktif => 'Diterima',
            self::Ditolak => 'Tidak diterima',
            self::Selesai => 'Selesai',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Menunggu => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
            self::Aktif => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700',
            self::Ditolak => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-300 dark:border-rose-700',
            self::Selesai => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700',
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Aktif => 'success',
            self::Ditolak => 'danger',
            self::Selesai => 'gray',
        };
    }
}
