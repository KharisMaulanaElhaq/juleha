<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'site_settings_cache_all';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Ambil seluruh setting dari cache sistem (0 query saat diakses berulang).
     *
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Ambil nilai satu setting.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();

        if (array_key_exists($key, $all)) {
            $value = $all[$key];

            return filled($value) ? $value : $default;
        }

        // Fallback kompatibilitas key email
        if ($key === 'contact_email' && array_key_exists('admin_email', $all) && filled($all['admin_email'])) {
            return $all['admin_email'];
        }

        if ($key === 'admin_email' && array_key_exists('contact_email', $all) && filled($all['contact_email'])) {
            return $all['contact_email'];
        }

        return $default;
    }

    /**
     * Simpan / perbarui setting dan otomatis invalidasi cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        static::clearCache();

        return $setting;
    }

    /**
     * Bersihkan cache setting saat ada pembaruan data.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
