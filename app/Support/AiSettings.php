<?php

namespace App\Support;

use App\Models\AppSetting;

class AiSettings
{
    public static function isEnabled(): bool
    {
        $stored = AppSetting::get('ai_enabled');

        if ($stored !== null) {
            return filter_var($stored, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) config('ai.enabled', false);
    }

    public static function isConfigured(): bool
    {
        return self::isEnabled() && filled(self::apiKey());
    }

    public static function apiKey(): ?string
    {
        return AppSetting::get('openai_api_key') ?: config('ai.openai.api_key');
    }

    public static function hasApiKey(): bool
    {
        return filled(self::apiKey());
    }

    public static function model(): string
    {
        return AppSetting::get('openai_model') ?: (string) config('ai.openai.model', 'gpt-4o-mini');
    }

    public static function baseUrl(): string
    {
        return (string) config('ai.openai.base_url', 'https://api.openai.com/v1');
    }

    public static function timeout(): int
    {
        return (int) config('ai.openai.timeout', 90);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toFormArray(): array
    {
        return [
            'ai_enabled'      => self::isEnabled(),
            'openai_api_key'  => '',
            'openai_model'    => self::model(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function updateFromForm(array $data): void
    {
        AppSetting::set('ai_enabled', isset($data['ai_enabled']) && $data['ai_enabled'] ? '1' : '0');

        if (filled($data['openai_api_key'] ?? null)) {
            AppSetting::set('openai_api_key', (string) $data['openai_api_key'], encrypt: true);
        }

        if (filled($data['openai_model'] ?? null)) {
            AppSetting::set('openai_model', (string) $data['openai_model']);
        }
    }
}
