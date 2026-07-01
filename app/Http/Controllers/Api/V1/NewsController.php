<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsSummaryResource;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use App\Services\News\NewsPdfService;
use App\Services\News\NewsWorkspaceService;
use App\Traits\AppliesTranslatableLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function related(Request $request, int $newsId): JsonResponse
    {
        try {
            $request->validate([
                'locale' => 'nullable|in:ar,en',
                'limit'  => 'nullable|integer|min:1|max:12',
            ]);

            $news = News::published()->find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            $locale = $this->resolveApiLocale($request);
            $limit = min((int) $request->input('limit', 6), 12);

            $siblingCategoryIds = $this->siblingCategoryIds($news->category_id);
            $hasCriteria = $news->category_id || $siblingCategoryIds->isNotEmpty() || $news->is_breaking;

            $related = News::published()
                ->withTranslation($locale)
                ->with(['author:id,name', 'category:id,name,slug'])
                ->where('id', '!=', $news->id)
                ->when($hasCriteria, function ($query) use ($news, $siblingCategoryIds) {
                    $query->where(function ($inner) use ($news, $siblingCategoryIds) {
                        if ($news->category_id) {
                            $inner->where('category_id', $news->category_id);
                        }

                        if ($siblingCategoryIds->isNotEmpty()) {
                            $inner->orWhereIn('category_id', $siblingCategoryIds);
                        }

                        if ($news->is_breaking) {
                            $inner->orWhere('is_breaking', true);
                        }
                    });
                })
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();

            return $this->success(
                NewsSummaryResource::collection($related)->resolve(),
                'Related news retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve related news.');
        }
    }

    public function trendingTopics(int $newsId): JsonResponse
    {
        try {
            $news = News::published()->find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            $category = $news->category_id
                ? Category::query()->find($news->category_id)
                : null;

            $topics = Category::query()
                ->select(['categories.id', 'categories.name', 'categories.slug'])
                ->selectRaw('COALESCE(SUM(news.views_count), 0) as total_views')
                ->selectRaw('COUNT(DISTINCT news.id) as news_count')
                ->join('news', 'categories.id', '=', 'news.category_id')
                ->where('news.status', 'published')
                ->when($category, function ($query) use ($category) {
                    if ($category->parent_id) {
                        $query->where('categories.parent_id', $category->parent_id);
                    } else {
                        $query->where(function ($inner) use ($category) {
                            $inner->where('categories.parent_id', $category->id)
                                ->orWhere('categories.id', $category->id);
                        });
                    }
                })
                ->groupBy('categories.id', 'categories.name', 'categories.slug')
                ->orderByDesc('total_views')
                ->limit(10)
                ->get();

            return $this->success($topics, 'Trending topics retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve trending topics.');
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

    public function myDrafts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $this->userCanManageNews($user)) {
                return $this->error(null, 'You are not authorized to manage news.', 403);
            }

            $request->validate([
                'all' => 'nullable|boolean',
            ]);

            return app(NewsWorkspaceService::class)->draftsResponse($request, $user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve news drafts.');
        }
    }

    public function myNews(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $this->userCanManageNews($user)) {
                return $this->error(null, 'You are not authorized to manage news.', 403);
            }

            $request->validate([
                'status'   => 'nullable|in:draft,under_review,published,archived',
                'category' => 'nullable|integer|exists:categories,id',
                'search'   => 'nullable|string|max:200',
                'sort'     => 'nullable|in:latest,oldest,views',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $result = app(NewsWorkspaceService::class)->paginatedList($request, $user);

            return $this->pagedSuccess(
                $result['items'],
                [
                    ...$result['meta'],
                    'summary' => $result['summary'],
                ],
                'My news retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve my news.');
        }
    }

    public function preview(Request $request, int $newsId): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $this->userCanManageNews($user)) {
                return $this->error(null, 'You are not authorized to manage news.', 403);
            }

            $response = app(NewsWorkspaceService::class)->previewResponse($user, $newsId);

            if ($response->getStatusCode() === 404) {
                return $this->error(null, 'News not found.', 404);
            }

            return $response;
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve news preview.');
        }
    }

    public function showWorkspace(Request $request, int $newsId): JsonResponse
    {
        return $this->preview($request, $newsId);
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
                'featured_image'     => $this->featuredImageRules(),
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
                'featured_image'     => $this->featuredImageRules(),
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

    private function featuredImageRules(): array
    {
        return [
            'nullable',
            'string',
            'max:500',
            'not_regex:/\.\./',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (blank($value)) {
                    return;
                }

                if (! is_string($value) || ! str_starts_with($value, 'uploads/')) {
                    $fail('The featured image path must start with uploads/ (upload the image first).');

                    return;
                }

                if (! Storage::disk('images')->exists($value)) {
                    $fail('The featured image was not found. Upload it first via POST /uploads/images.');
                }
            },
        ];
    }

    private function siblingCategoryIds(?int $categoryId): \Illuminate\Support\Collection
    {
        if (! $categoryId) {
            return collect();
        }

        $category = Category::query()->find($categoryId);

        if (! $category) {
            return collect();
        }

        if ($category->parent_id) {
            return Category::query()
                ->where('parent_id', $category->parent_id)
                ->where('id', '!=', $categoryId)
                ->pluck('id');
        }

        return Category::query()
            ->where('parent_id', $categoryId)
            ->pluck('id');
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
