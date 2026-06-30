<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Translatable
{
    abstract public function translationModelClass(): string;

    public function translatedAttributeNames(): array
    {
        return property_exists($this, 'translatedAttributes')
            ? $this->translatedAttributes
            : [];
    }

    protected static function bootTranslatable(): void
    {
        static::saving(function (Model $model) {
            if (! $model->relationLoaded('translations')) {
                $model->load('translations');
            }
        });

        static::saved(function (Model $model) {
            if (! $model->relationLoaded('translations')) {
                return;
            }

            foreach ($model->translations as $translation) {
                if (! $translation->exists || $translation->isDirty()) {
                    $translation->setAttribute($model->getForeignKey(), $model->getKey());
                    $translation->save();
                }
            }
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany($this->translationModelClass());
    }

    public function translate(?string $locale = null, bool $withFallback = true): ?Model
    {
        $locale = $locale ?? app()->getLocale();

        $translation = $this->relationLoaded('translations')
            ? $this->translations->firstWhere('locale', $locale)
            : $this->translations()->where('locale', $locale)->first();

        if ($translation || ! $withFallback) {
            return $translation;
        }

        $fallback = config('app.fallback_locale', 'en');

        if ($fallback === $locale) {
            return null;
        }

        return $this->relationLoaded('translations')
            ? $this->translations->firstWhere('locale', $fallback)
            : $this->translations()->where('locale', $fallback)->first();
    }

    public function translateOrNew(?string $locale = null): Model
    {
        $locale = $locale ?? app()->getLocale();

        $translation = $this->translate($locale, false);

        if ($translation) {
            return $translation;
        }

        $translation = $this->translations()->make([
            'locale' => $locale,
        ]);

        if ($this->relationLoaded('translations')) {
            $this->translations->push($translation);
        } else {
            $this->setRelation('translations', collect([$translation]));
        }

        return $translation;
    }

    public function scopeWithTranslation(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();

        return $query->with(['translations' => fn ($q) => $q->where('locale', $locale)]);
    }

    public function scopeTranslatedIn(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn ($q) => $q->where('locale', $locale));
    }

    public function scopeWhereTranslation(Builder $query, string $field, mixed $value, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->whereHas('translations', fn ($q) => $q
            ->where('locale', $locale)
            ->where($field, $value));
    }

    public function scopeWhereTranslationLike(Builder $query, string $field, string $value, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->whereHas('translations', fn ($q) => $q
            ->where('locale', $locale)
            ->where($field, 'like', $value));
    }

    public function getTranslatedAttribute(string $attribute, ?string $locale = null): mixed
    {
        return $this->translate($locale)?->{$attribute};
    }

    public function setTranslatedAttribute(string $attribute, mixed $value, string $locale): void
    {
        $this->translateOrNew($locale)->{$attribute} = $value;
    }
}
