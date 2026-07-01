<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsSummaryResource;
use App\Models\News;
use App\Models\User;
use App\Services\News\NewsPdfService;
use App\Traits\AppliesTranslatableLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NewsController extends Controller
{
    use AppliesTranslatableLocale;

    public function index(Request $request): JsonResponse
    {
        try {
            $locale = $this->resolveApiLocale($request);

            $query = News::published()
                ->withTranslation($locale)
                ->with(['author:id,name', 'category:id,name,slug']);

            if ($request->filled('category')) {
                $query->where('category_id', $request->input('category'));
            }

            if ($request->filled('locale')) {
                $query->translatedIn($request->input('locale'));
            }

            if ($request->boolean('breaking')) {
                $query->where('is_breaking', true);
            }

            if ($request->boolean('premium')) {
                $query->where('is_premium', true);
            }

            if ($request->filled('search')) {
                $this->applyTranslationSearch($query, $request->input('search'), $request->input('locale'));
            }

            match ($request->input('sort', 'latest')) {
                'views' => $query->orderByDesc('views_count'),
                'oldest' => $query->orderBy('published_at'),
                default => $query->orderByDesc('published_at'),
            };

            $paginator = $query->paginate($request->input('per_page', 15));

            return $this->pagedSuccess(
                NewsSummaryResource::collection($paginator->items())->resolve(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
                'News retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve news.');
        }
    }

    public function show(Request $request, int $newsId): JsonResponse
    {
        try {
            $locale = $this->resolveApiLocale($request);

            $news = News::published()
                ->withTranslation($locale)
                ->with(['author:id,name', 'category:id,name,slug', 'translations'])
                ->where('id', $newsId)
                ->first();

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            $news->increment('views_count');

            $translation = $news->translate($locale, false) ?? $news->translate($locale);

            return $this->success([
                ...(new NewsSummaryResource($news))->toArray($request),
                'content' => $translation?->content,
                'seo_title' => $translation?->seo_title,
                'seo_description' => $translation?->seo_description,
                'video_embed' => $news->video_embed,
            ], 'News retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve news.');
        }
    }

    public function downloadPdf(Request $request, int $newsId)
    {
        try {
            $request->validate([
                'locale' => 'nullable|in:ar,en',
            ]);

            $locale = $this->resolveApiLocale($request);

            $news = News::published()
                ->withTranslation($locale)
                ->with(['author', 'category', 'translations'])
                ->where('id', $newsId)
                ->first();

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            if (! $news->translate($locale, false)) {
                return $this->error(null, 'News translation not found for the requested locale.', 404);
            }

            return app(NewsPdfService::class)->download($news, $locale);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate news PDF.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $this->userCanManageNews($user)) {
                return $this->error(null, 'You are not authorized to manage news.', 403);
            }

            $data = $request->validate([
                'category_id'        => 'nullable|exists:categories,id',
                'title_en'           => 'nullable|string|max:500',
                'title_ar'           => 'nullable|string|max:500',
                'subtitle_en'        => 'nullable|string|max:500',
                'subtitle_ar'        => 'nullable|string|max:500',
                'slug_en'            => 'nullable|string|max:500|unique:news_translations,slug,NULL,id,locale,en',
                'slug_ar'            => 'nullable|string|max:500|unique:news_translations,slug,NULL,id,locale,ar',
                'content_en'         => 'nullable|string',
                'content_ar'         => 'nullable|string',
                'excerpt_en'         => 'nullable|string|max:1000',
                'excerpt_ar'         => 'nullable|string|max:1000',
                'title'              => 'nullable|string|max:500',
                'subtitle'           => 'nullable|string|max:500',
                'slug'               => 'nullable|string|max:500',
                'content'            => 'nullable|string',
                'excerpt'            => 'nullable|string|max:1000',
                'featured_image'     => 'nullable|string',
                'video_embed'        => 'nullable|string',
                'locale'             => 'nullable|in:ar,en',
                'read_time'          => 'nullable|integer|min:1',
                'is_breaking'        => 'boolean',
                'is_premium'         => 'boolean',
                'seo_title_en'       => 'nullable|string|max:200',
                'seo_title_ar'       => 'nullable|string|max:200',
                'seo_description_en' => 'nullable|string|max:400',
                'seo_description_ar' => 'nullable|string|max:400',
                'seo_title'          => 'nullable|string|max:200',
                'seo_description'    => 'nullable|string|max:400',
                'status'             => 'nullable|in:draft,under_review,published,archived',
                'published_at'       => 'nullable|date',
            ]);

            $this->mapLegacyTranslationInput($data);

            $status = $this->resolveNewsStatusForUser($user, $data['status'] ?? 'draft');
            unset($data['status']);

            $news = News::create([
                ...$this->extractNewsBaseAttributes($data),
                'author_id'    => $user->id,
                'status'       => $status,
                'published_at' => $this->resolvePublishedAt($status, $data['published_at'] ?? null),
            ]);

            $this->fillNewsTranslations($news, $data);

            return $this->success(
                new NewsSummaryResource($news->load(['author', 'category', 'translations'])),
                'News created successfully.',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to create news.');
        }
    }

    public function update(Request $request, int $newsId): JsonResponse
    {
        try {
            $user = $request->user();
            $news = News::find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            if (! $this->userCanEditNews($user, $news)) {
                return $this->error(null, 'You are not authorized to edit this news item.', 403);
            }

            $data = $request->validate([
                'category_id'        => 'sometimes|nullable|exists:categories,id',
                'title_en'           => 'nullable|string|max:500',
                'title_ar'           => 'nullable|string|max:500',
                'subtitle_en'        => 'nullable|string|max:500',
                'subtitle_ar'        => 'nullable|string|max:500',
                'slug_en'            => 'nullable|string|max:500',
                'slug_ar'            => 'nullable|string|max:500',
                'content_en'         => 'nullable|string',
                'content_ar'         => 'nullable|string',
                'excerpt_en'         => 'nullable|string|max:1000',
                'excerpt_ar'         => 'nullable|string|max:1000',
                'title'              => 'nullable|string|max:500',
                'subtitle'           => 'nullable|string|max:500',
                'slug'               => 'nullable|string|max:500',
                'content'            => 'nullable|string',
                'excerpt'            => 'nullable|string|max:1000',
                'featured_image'     => 'nullable|string',
                'video_embed'        => 'nullable|string',
                'locale'             => 'nullable|in:ar,en',
                'read_time'          => 'nullable|integer|min:1',
                'is_breaking'        => 'boolean',
                'is_premium'         => 'boolean',
                'seo_title_en'       => 'nullable|string|max:200',
                'seo_title_ar'       => 'nullable|string|max:200',
                'seo_description_en' => 'nullable|string|max:400',
                'seo_description_ar' => 'nullable|string|max:400',
                'seo_title'          => 'nullable|string|max:200',
                'seo_description'    => 'nullable|string|max:400',
                'status'             => 'nullable|in:draft,under_review,published,archived',
                'published_at'       => 'nullable|date',
            ]);

            $this->mapLegacyTranslationInput($data);

            if (array_key_exists('status', $data)) {
                $status = $this->resolveNewsStatusForUser($user, $data['status'], $news);
                $data['published_at'] = $this->resolvePublishedAt(
                    $status,
                    $data['published_at'] ?? $news->published_at?->toDateTimeString(),
                    $news
                );
                $data['status'] = $status;
            }

            $news->fill($this->extractNewsBaseAttributes($data))->save();
            $this->fillNewsTranslations($news, $data);

            return $this->success(
                new NewsSummaryResource($news->load(['author', 'category', 'translations'])),
                'News updated successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update news.');
        }
    }

    public function destroy(Request $request, int $newsId): JsonResponse
    {
        try {
            $user = $request->user();
            $news = News::find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            if (! $this->userCanDeleteNews($user, $news)) {
                return $this->error(null, 'You are not authorized to delete this news item.', 403);
            }

            $news->delete();

            return $this->success(null, 'News deleted successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete news.');
        }
    }

    private function extractNewsBaseAttributes(array $data): array
    {
        return collect($data)->only([
            'category_id',
            'featured_image',
            'video_embed',
            'read_time',
            'is_breaking',
            'is_premium',
            'status',
            'published_at',
        ])->all();
    }

    private function fillNewsTranslations(News $news, array $data): void
    {
        foreach ($news->translatedAttributeNames() as $field) {
            foreach (['en', 'ar'] as $locale) {
                $key = "{$field}_{$locale}";
                if (array_key_exists($key, $data)) {
                    $news->setAttribute($key, $data[$key]);
                }
            }
        }

        $news->save();
    }

    private function mapLegacyTranslationInput(array &$data): void
    {
        if (! isset($data['locale'])) {
            return;
        }

        $locale = $data['locale'];

        foreach (['title', 'subtitle', 'slug', 'content', 'excerpt', 'seo_title', 'seo_description'] as $field) {
            if (array_key_exists($field, $data) && ! array_key_exists("{$field}_{$locale}", $data)) {
                $data["{$field}_{$locale}"] = $data[$field];
            }
        }
    }

    private function resolvePublishedAt(string $status, ?string $publishedAt, ?News $existing = null): ?\Illuminate\Support\Carbon
    {
        if ($status !== 'published') {
            return null;
        }

        if ($publishedAt) {
            return \Illuminate\Support\Carbon::parse($publishedAt);
        }

        return $existing?->published_at ?? now();
    }

    private function userCanManageNews(User $user): bool
    {
        return $user->writer()->exists()
            || $user->contributor()->exists()
            || $user->editor()->exists()
            || $user->admin()->exists();
    }

    private function userIsNewsEditor(User $user): bool
    {
        return $user->editor()->exists() || $user->admin()->exists();
    }

    private function userCanEditNews(User $user, News $news): bool
    {
        if ($this->userIsNewsEditor($user)) {
            return true;
        }

        return $news->author_id === $user->id
            && ($user->writer()->exists() || $user->contributor()->exists());
    }

    private function userCanDeleteNews(User $user, News $news): bool
    {
        if ($user->admin()->exists()) {
            return true;
        }

        return $news->author_id === $user->id
            && ($user->writer()->exists() || $user->contributor()->exists());
    }

    private function resolveNewsStatusForUser(User $user, string $status, ?News $existing = null): string
    {
        if ($this->userIsNewsEditor($user)) {
            return $status;
        }

        if (! in_array($status, ['draft', 'under_review'], true)) {
            return $existing?->status && in_array($existing->status, ['draft', 'under_review'], true)
                ? $existing->status
                : 'draft';
        }

        return $status;
    }
}
