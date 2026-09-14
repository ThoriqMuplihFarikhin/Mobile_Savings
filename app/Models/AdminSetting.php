<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $key
 * @property string|null $value
 */
#[Fillable(['key', 'value'])]
class AdminSetting extends Model
{
    protected static array $encryptedKeys = ['wa_api_key'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("admin_setting:{$key}", function () use ($key, $default) {
            $raw = static::where('key', $key)->value('value') ?? $default;

            if ($raw && in_array($key, static::$encryptedKeys, true)) {
                try {
                    return Crypt::decryptString($raw);
                } catch (DecryptException) {
                    return $raw;
                }
            }

            return $raw;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        $stored = in_array($key, static::$encryptedKeys, true) && $value !== null
            ? Crypt::encryptString($value)
            : $value;

        static::updateOrCreate(['key' => $key], ['value' => $stored]);

        Cache::forget("admin_setting:{$key}");
    }
}
