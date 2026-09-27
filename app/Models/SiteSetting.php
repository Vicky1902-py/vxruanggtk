<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Helper pengaturan situs (key-value) dengan cache per-request.
 */
class SiteSetting
{
    protected static array $cache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, static::$cache)) {
            try {
                static::$cache[$key] = optional(
                    DB::table('site_settings')->where('key', $key)->first()
                )->value;
            } catch (\Throwable) {
                // Tabel belum ada (mis. saat instalasi/test) — pakai default.
                static::$cache[$key] = null;
            }
        }

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        DB::table('site_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
        static::$cache[$key] = $value;
    }
}
