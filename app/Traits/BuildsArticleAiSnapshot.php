<?php

namespace App\Traits;

use App\Models\Article;

trait BuildsArticleAiSnapshot
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function buildImprovementSnapshot(array $input, ?Article $article = null): array
    {
        $locale = (string) ($input['locale'] ?? $this->detectPrimaryLocale($input, $article) ?? 'ar');

        $snapshot = ['locale' => $locale];

        foreach (['title', 'subtitle', 'content', 'excerpt', 'seo_title', 'seo_description'] as $field) {
            $localizedKey = "{$field}_{$locale}";

            if (array_key_exists($localizedKey, $input)) {
                $snapshot[$field] = $input[$localizedKey];
            } elseif (array_key_exists($field, $input)) {
                $snapshot[$field] = $input[$field];
            } elseif ($article) {
                $snapshot[$field] = $article->translate($locale, false)?->{$field};
            }
        }

        return array_filter(
            $snapshot,
            fn ($value) => $value !== null && $value !== ''
        );
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function buildTranslationSnapshot(array $input, ?Article $article = null): array
    {
        $sourceLocale = $this->resolveSourceLocale($input, $article);
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
            } elseif ($article) {
                $snapshot[$field] = $article->translate($sourceLocale, false)?->{$field};
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
    protected function resolveSourceLocale(array $input, ?Article $article = null): string
    {
        if (! empty($input['source_locale']) && in_array($input['source_locale'], ['ar', 'en'], true)) {
            return $input['source_locale'];
        }

        if (! empty($input['locale']) && in_array($input['locale'], ['ar', 'en'], true)) {
            return $input['locale'];
        }

        $detected = $this->detectPrimaryLocale($input, $article);

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
    protected function detectPrimaryLocale(array $input, ?Article $article = null): ?string
    {
        foreach (['ar', 'en'] as $locale) {
            foreach (['title', 'content', 'excerpt'] as $field) {
                $key = "{$field}_{$locale}";

                if (filled($input[$key] ?? null)) {
                    return $locale;
                }
            }
        }

        if ($article) {
            $article->loadMissing('translations');

            foreach (['ar', 'en'] as $locale) {
                $translation = $article->translations->firstWhere('locale', $locale);

                if (filled($translation?->title) || filled($translation?->content) || filled($translation?->excerpt)) {
                    return $locale;
                }
            }
        }

        return null;
    }
}
