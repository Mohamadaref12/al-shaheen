<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function get(string $key): ?string
    {
        $setting = static::query()->find($key);

        if (! $setting || $setting->value === null) {
            return null;
        }

        if ($setting->is_encrypted) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Throwable) {
                return null;
            }
        }

        return $setting->value;
    }

    public static function set(string $key, ?string $value, bool $encrypt = false): void
    {
        if ($value === null || $value === '') {
            static::query()->where('key', $key)->delete();

            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value'        => $encrypt ? Crypt::encryptString($value) : $value,
                'is_encrypted' => $encrypt,
            ]
        );
    }

    public static function has(string $key): bool
    {
        return static::query()->where('key', $key)->exists();
    }
}
