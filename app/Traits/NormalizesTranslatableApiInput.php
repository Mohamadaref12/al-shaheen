<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait NormalizesTranslatableApiInput
{
    protected function prepareTranslatableRequest(Request $request): void
    {
        $payload = $request->all();

        if (! array_key_exists('tags', $payload) && array_key_exists('tag_ids', $payload)) {
            $payload['tags'] = $payload['tag_ids'];
        }

        if (array_key_exists('tags', $payload)) {
            $payload['tags'] = $this->normalizeTagIds($payload['tags']);
        }

        foreach (['content', 'excerpt'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = $this->normalizeRichTextValue($payload[$field]);
            }

            foreach (['ar', 'en'] as $locale) {
                $key = "{$field}_{$locale}";
                if (array_key_exists($key, $payload)) {
                    $payload[$key] = $this->normalizeRichTextValue($payload[$key]);
                }
            }
        }

        $request->merge($payload);
    }

    protected function translatableRichTextRules(): array
    {
        return [
            'nullable',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || is_string($value) || is_array($value)) {
                    return;
                }

                $fail("The {$attribute} field must be a string or JSON object.");
            },
        ];
    }

    protected function normalizeRichTextValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: null;
        }

        return null;
    }

    /**
     * @return list<int>
     */
    protected function normalizeTagIds(mixed $tags): array
    {
        if ($tags === null || $tags === '') {
            return [];
        }

        if (is_string($tags)) {
            $tags = array_filter(array_map('trim', explode(',', $tags)));
        }

        if (! is_array($tags)) {
            return [];
        }

        return collect($tags)
            ->map(function (mixed $tag): ?int {
                if (is_numeric($tag)) {
                    return (int) $tag;
                }

                if (is_array($tag)) {
                    if (isset($tag['id']) && is_numeric($tag['id'])) {
                        return (int) $tag['id'];
                    }

                    if (isset($tag['slug']) && is_string($tag['slug'])) {
                        return Tag::query()->where('slug', $tag['slug'])->value('id');
                    }
                }

                if (is_string($tag)) {
                    return Tag::query()
                        ->where('slug', $tag)
                        ->orWhere('name', $tag)
                        ->value('id');
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function mapLegacyTranslationInput(array &$data, ?string $defaultLocale = null): void
    {
        $locale = $data['locale'] ?? $defaultLocale ?? config('app.locale', 'ar');
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';

        foreach (['title', 'subtitle', 'slug', 'content', 'excerpt', 'seo_title', 'seo_description'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if (array_key_exists("{$field}_{$locale}", $data)) {
                continue;
            }

            $value = $data[$field];

            if (in_array($field, ['content', 'excerpt'], true)) {
                $value = $this->normalizeRichTextValue($value);
            }

            $data["{$field}_{$locale}"] = $value;
        }
    }

    protected function persistModelTranslations(Model $model): void
    {
        if (! $model->relationLoaded('translations')) {
            $model->load('translations');
        }

        foreach ($model->translations as $translation) {
            if (! $translation->exists || $translation->isDirty()) {
                $this->ensureTranslationRequiredFields($translation, $model);
                $translation->setAttribute($model->getForeignKey(), $model->getKey());
                $translation->save();
            }
        }
    }

    protected function ensureTranslationRequiredFields(Model $translation, Model $parent): void
    {
        $locale = (string) ($translation->locale ?? config('app.locale', 'ar'));
        $parentKey = $parent->getKey();
        $type = Str::singular($parent->getTable());

        if (blank($translation->title)) {
            if (filled($translation->slug)) {
                $translation->title = Str::limit((string) $translation->slug, 500, '');
            } elseif (filled($translation->excerpt)) {
                $translation->title = Str::limit(strip_tags((string) $translation->excerpt), 500, '');
            } else {
                $translation->title = Str::limit("{$type}-{$parentKey}-{$locale}", 500, '');
            }
        }

        if (blank($translation->slug)) {
            $base = (string) $translation->title;
            $translation->slug = $this->uniqueTranslationSlug(
                $translation->getTable(),
                $locale,
                $base,
                $translation->exists ? $translation->getKey() : null
            );
        }
    }

    protected function uniqueTranslationSlug(
        string $table,
        string $locale,
        string $base,
        ?int $ignoreId = null
    ): string {
        $slug = Str::slug(Str::ascii($base)) ?: 'item';
        $slug = Str::limit($slug, 480, '');
        $candidate = $slug;
        $suffix = 1;

        while (
            DB::table($table)
                ->where('locale', $locale)
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = Str::limit($slug, 470, '') . '-' . $suffix++;
        }

        return $candidate;
    }
}
