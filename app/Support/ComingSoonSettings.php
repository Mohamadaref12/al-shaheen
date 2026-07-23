<?php

namespace App\Support;

use App\Models\AppSetting;

class ComingSoonSettings
{
    public static function isEnabled(): bool
    {
        $stored = AppSetting::get('coming_soon_enabled');

        if ($stored !== null) {
            return filter_var($stored, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) config('coming_soon.enabled', false);
    }

    public static function accessKey(): string
    {
        return (string) (AppSetting::get('coming_soon_access_key')
            ?: config('coming_soon.access_key', ''));
    }

    public static function hasAccessKey(): bool
    {
        return filled(self::accessKey());
    }

    public static function header(): string
    {
        return (string) config('coming_soon.header', 'X-Coming-Soon-Key');
    }

    public static function cookieName(): string
    {
        return (string) config('coming_soon.cookie', 'coming_soon_access');
    }

    public static function cookieMinutes(): int
    {
        return (int) config('coming_soon.cookie_minutes', 60 * 24 * 30);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toFormArray(): array
    {
        return [
            'coming_soon_enabled'    => self::isEnabled(),
            'coming_soon_access_key' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function updateFromForm(array $data): void
    {
        AppSetting::set(
            'coming_soon_enabled',
            isset($data['coming_soon_enabled']) && $data['coming_soon_enabled'] ? '1' : '0'
        );

        if (filled($data['coming_soon_access_key'] ?? null)) {
            AppSetting::set(
                'coming_soon_access_key',
                (string) $data['coming_soon_access_key'],
                encrypt: true
            );
        }
    }
}
