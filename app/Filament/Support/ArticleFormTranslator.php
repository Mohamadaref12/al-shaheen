<?php

namespace App\Filament\Support;

use App\Actions\GenerateArticleTranslationAction;
use App\Models\Article;
use App\Models\User;
use App\Support\ArticleContent;
use Illuminate\Support\Str;

class ArticleFormTranslator
{
    /**
     * @var array<int, string>
     */
    private const FIELDS = [
        'title',
        'subtitle',
        'slug',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
    ];

    public function __construct(
        protected GenerateArticleTranslationAction $generateTranslation,
    ) {}

    /**
     * @param  array<string, mixed>  $state
     * @return array{
     *     success: bool,
     *     message: string,
     *     updates?: array<string, mixed>,
     *     source_locale?: string,
     *     target_locale?: string
     * }
     */
    public function translate(array $state, User $user, ?Article $article = null): array
    {
        $locales = $this->resolveLocales($state);

        if ($locales === null) {
            return [
                'success' => false,
                'message' => __('filament.actions.translate_article_no_source'),
            ];
        }

        ['source' => $sourceLocale, 'target' => $targetLocale] = $locales;

        $input = array_merge(ArticleContent::normalizeFormStateForAi($state), [
            'source_locale' => $sourceLocale,
            'target_locale' => $targetLocale,
        ]);

        $result = $this->generateTranslation->execute($user, $input, $article);

        if (! $result['available']) {
            return [
                'success' => false,
                'message' => $result['message'],
            ];
        }

        if (! $result['suggestion']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? __('filament.actions.translate_article_failed'),
            ];
        }

        $suggestions = (array) ($result['suggestion']->suggestions ?? []);
        $updates = $this->buildFormUpdates($state, $suggestions, $targetLocale);

        if ($updates === []) {
            return [
                'success' => false,
                'message' => __('filament.actions.translate_article_nothing_to_fill'),
            ];
        }

        return [
            'success'       => true,
            'message'       => __('filament.actions.translate_article_success', [
                'from' => $sourceLocale === 'ar' ? __('filament.actions.locale_ar') : __('filament.actions.locale_en'),
                'to'   => $targetLocale === 'ar' ? __('filament.actions.locale_ar') : __('filament.actions.locale_en'),
            ]),
            'updates'       => $updates,
            'source_locale' => $sourceLocale,
            'target_locale' => $targetLocale,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array{source: string, target: string}|null
     */
    protected function resolveLocales(array $state): ?array
    {
        $arScore = $this->localeContentScore($state, 'ar');
        $enScore = $this->localeContentScore($state, 'en');

        if ($arScore === 0 && $enScore === 0) {
            return null;
        }

        if ($arScore > 0 && $enScore === 0) {
            return ['source' => 'ar', 'target' => 'en'];
        }

        if ($enScore > 0 && $arScore === 0) {
            return ['source' => 'en', 'target' => 'ar'];
        }

        if ($arScore > $enScore) {
            return ['source' => 'ar', 'target' => 'en'];
        }

        if ($enScore > $arScore) {
            return ['source' => 'en', 'target' => 'ar'];
        }

        $arEmptyCount = $this->countEmptyFields($state, 'ar');
        $enEmptyCount = $this->countEmptyFields($state, 'en');

        if ($arEmptyCount > $enEmptyCount) {
            return ['source' => 'en', 'target' => 'ar'];
        }

        if ($enEmptyCount > $arEmptyCount) {
            return ['source' => 'ar', 'target' => 'en'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function localeContentScore(array $state, string $locale): int
    {
        $weights = [
            'title'           => 3,
            'content'         => 5,
            'excerpt'         => 2,
            'subtitle'        => 1,
            'seo_title'       => 1,
            'seo_description' => 1,
        ];

        $score = 0;

        foreach ($weights as $field => $weight) {
            if ($this->fieldHasValue($state, $locale, $field)) {
                $score += $weight;
            }
        }

        return $score;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function countEmptyFields(array $state, string $locale): int
    {
        $empty = 0;

        foreach (self::FIELDS as $field) {
            if ($this->fieldIsEmpty($state, $locale, $field)) {
                $empty++;
            }
        }

        return $empty;
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $suggestions
     * @return array<string, mixed>
     */
    protected function buildFormUpdates(array $state, array $suggestions, string $targetLocale): array
    {
        $updates = [];

        foreach (self::FIELDS as $field) {
            if ($field === 'slug') {
                continue;
            }

            $key = "{$field}_{$targetLocale}";

            if (! $this->fieldIsEmpty($state, $targetLocale, $field)) {
                continue;
            }

            $value = $suggestions[$key] ?? $suggestions[$field] ?? null;

            if ($this->fieldHasValue([$key => $value], $targetLocale, $field)) {
                $updates[$key] = $value;
            }
        }

        $titleKey = "title_{$targetLocale}";
        $slugKey = "slug_{$targetLocale}";

        if ($this->fieldIsEmpty($state, $targetLocale, 'slug')) {
            $title = $updates[$titleKey] ?? $state[$titleKey] ?? null;

            if (filled($title)) {
                $updates[$slugKey] = $this->generateSlug((string) $title, $targetLocale, $state, $targetLocale);
            }
        }

        return $updates;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function fieldIsEmpty(array $state, string $locale, string $field): bool
    {
        $value = $state["{$field}_{$locale}"] ?? null;

        if ($field === 'content') {
            return ArticleContent::isBlank($value);
        }

        return blank($value);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function fieldHasValue(array $state, string $locale, string $field): bool
    {
        return ! $this->fieldIsEmpty($state, $locale, $field);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function generateSlug(string $title, string $locale, array $state, string $targetLocale): string
    {
        $slug = Str::slug($title);

        if (filled($slug)) {
            return $slug;
        }

        $otherLocale = $targetLocale === 'ar' ? 'en' : 'ar';
        $fallback = $state["slug_{$otherLocale}"] ?? null;

        if (filled($fallback)) {
            return "{$fallback}-{$targetLocale}";
        }

        return 'article-'.Str::lower(Str::random(8));
    }
}
