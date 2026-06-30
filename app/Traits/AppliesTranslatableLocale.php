<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesTranslatableLocale
{
    protected function resolveApiLocale(Request $request): string
    {
        $locale = $request->input('locale', config('app.locale', 'ar'));

        return in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';
    }

    protected function applyTranslationLocale(Builder $query, Request $request): Builder
    {
        $locale = $this->resolveApiLocale($request);

        return $query
            ->withTranslation($locale)
            ->translatedIn($locale);
    }

    protected function applyTranslationSearch(Builder $query, string $term, ?string $locale = null): Builder
    {
        $locales = $locale ? [$locale] : ['ar', 'en'];

        return $query->where(function (Builder $builder) use ($term, $locales) {
            foreach ($locales as $loc) {
                $builder->orWhereHas('translations', fn (Builder $q) => $q
                    ->where('locale', $loc)
                    ->where(fn (Builder $inner) => $inner
                        ->where('title', 'like', "%{$term}%")
                        ->orWhere('excerpt', 'like', "%{$term}%")));
            }
        });
    }
}
