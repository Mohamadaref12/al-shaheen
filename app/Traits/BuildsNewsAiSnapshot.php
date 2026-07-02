<?php

namespace App\Traits;

use App\Models\News;

trait BuildsNewsAiSnapshot
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function buildTranslationSnapshot(array $input, ?News $news = null): array
    {
        $sourceLocale = $this->resolveSourceLocale($input, $news);
        $targetLocale = $this->resolveTargetLocale($input, $sourceLocale);

        if ($sourceLocale === $targetLocale) {
            $targetLocale = $sourceLocale === 'ar' ? 'en' : 'ar';
        }

        $snapshot = [
            'source_locale' => $sourceLocale,
            'target_locale' => $targetLocale,
        ];

        foreach (['title', 'subtitle', 'content', 'excerpt', 'seo_title', 'seo_description'] as $field) {
            $localizedKey = "{$field}_{$sourceLocale}";

            if (array_key_exists($localizedKey, $input)) {
                $snapshot[$field] = $input[$localizedKey];
            } elseif ($sourceLocale === ($input['locale'] ?? null) && array_key_exists($field, $input)) {
                $snapshot[$field] = $input[$field];
            } elseif ($news) {
                $snapshot[$field] = $news->translate($sourceLocale, false)?->{$field};
            }
        }

        return array_filter(
            $snapshot,
            fn ($value) => $value !== null && $value !== ''
        );
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function resolveSourceLocale(array $input, ?News $news = null): string
    {
        if (! empty($input['source_locale']) && in_array($input['source_locale'], ['ar', 'en'], true)) {
            return $input['source_locale'];
        }

        if (! empty($input['locale']) && in_array($input['locale'], ['ar', 'en'], true)) {
            return $input['locale'];
        }

        $detected = $this->detectPrimaryLocale($input, $news);

        return $detected ?? 'ar';
    }

    protected function resolveTargetLocale(array $input, string $sourceLocale): string
    {
        if (! empty($input['target_locale']) && in_array($input['target_locale'], ['ar', 'en'], true)) {
            return $input['target_locale'];
        }

        return $sourceLocale === 'ar' ? 'en' : 'ar';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function detectPrimaryLocale(array $input, ?News $news = null): ?string
    {
        foreach (['ar', 'en'] as $locale) {
            foreach (['title', 'content', 'excerpt'] as $field) {
                $key = "{$field}_{$locale}";

                if (filled($input[$key] ?? null)) {
                    return $locale;
                }
            }
        }

        if ($news) {
            $news->loadMissing('translations');

            foreach (['ar', 'en'] as $locale) {
                $translation = $news->translations->firstWhere('locale', $locale);

                if (filled($translation?->title) || filled($translation?->content) || filled($translation?->excerpt)) {
                    return $locale;
                }
            }
        }

        return null;
    }
}
