<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleSummaryResource;
use App\Models\Article;
use App\Models\Tag;
use App\Services\Articles\ArticleFeaturedImageDownloadService;
use App\Services\Articles\ArticlePdfService;
use App\Traits\AppliesTranslatableLocale;
use App\Traits\NormalizesTranslatableApiInput;
use App\Traits\MarksSavedArticles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ArticleController extends Controller
{
    use AppliesTranslatableLocale;
    use NormalizesTranslatableApiInput;
    use MarksSavedArticles;
    public function index(Request $request): JsonResponse
    {
        
        try {
            $locale = $this->resolveApiLocale($request);

            $query = Article::withTranslation($locale)
                ->with(['author:id,name', 'primaryCategory:id,name,slug', 'tags:id,name,slug'])
                ->where('status', 'published');

            if ($request->filled('category')) {
                $query->where('primary_category_id', $request->input('category'));
            }
            if ($request->filled('tag')) {
                $search = $request->input('tag');
                $query->whereHas('tags', fn ($q) => $q->where('slug', $search));
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
                'views'  => $query->orderByDesc('views_count'),
                'oldest' => $query->orderBy('published_at'),
                default  => $query->orderByDesc('published_at'),
            };

            $paginator = $this->withIsSavedOnPaginator(
                $query->paginate($request->input('per_page', 15)),
                $request
            );

            return $this->pagedSuccess(
                ArticleSummaryResource::collection($paginator->items())->resolve(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Articles retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve articles.');
        }
    }

    public function show(Request $request, int $articleId): JsonResponse
    {
        try {
            $locale = $this->resolveApiLocale($request);

            $article = Article::withTranslation($locale)
                ->with([
                'author:id,name',
                'primaryCategory:id,name,slug',
                'secondaryCategories:id,name,slug',
                'tags:id,name,slug',
                'translations',
            ])
                ->where('id', $articleId)
                ->where('status', 'published')
                ->first();

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $article->increment('views_count');

            return $this->success(
                $this->formatArticleDetailForApi($this->withIsSaved($article, $request), $request),
                'Article retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve article.');
        }
    }

    public function downloadPdf(Request $request, int $articleId)
    {
        try {
            $request->validate([
                'locale' => 'nullable|in:ar,en',
            ]);

            $locale = $this->resolveApiLocale($request);

            $article = Article::published()
                ->withTranslation($locale)
                ->with(['author', 'primaryCategory', 'tags', 'translations'])
                ->where('id', $articleId)
                ->first();

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            if (! $article->translate($locale, false)) {
                return $this->error(null, 'Article translation not found for the requested locale.', 404);
            }

            return app(ArticlePdfService::class)->download($article, $locale);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate article PDF.');
        }
    }

    public function downloadFeaturedImage(Request $request, int $articleId)
    {
        try {
            $request->validate([
                'locale' => 'nullable|in:ar,en',
                'inline' => 'nullable|boolean',
            ]);

            $locale = $this->resolveApiLocale($request);

            $article = Article::published()
                ->withTranslation($locale)
                ->with(['translations'])
                ->where('id', $articleId)
                ->first();

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            return app(ArticleFeaturedImageDownloadService::class)->download(
                $article,
                $locale,
                $request->boolean('inline')
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to download article image.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user->writer()->exists() && ! $user->editor()->exists() && ! $user->admin()->exists()) {
                return $this->error(null, 'You are not authorized to publish articles.', 403);
            }

            $this->prepareTranslatableRequest($request);

            $data = $request->validate([
                'primary_category_id'    => 'required|exists:categories,id',
                'title_en'               => 'nullable|string|max:500',
                'title_ar'               => 'nullable|string|max:500',
                'subtitle_en'            => 'nullable|string|max:500',
                'subtitle_ar'            => 'nullable|string|max:500',
                'slug_en'                => 'nullable|string|max:500|unique:article_translations,slug,NULL,id,locale,en',
                'slug_ar'                => 'nullable|string|max:500|unique:article_translations,slug,NULL,id,locale,ar',
                'content_en'             => $this->translatableRichTextRules(),
                'content_ar'             => $this->translatableRichTextRules(),
                'excerpt_en'             => $this->translatableRichTextRules(),
                'excerpt_ar'             => $this->translatableRichTextRules(),
                'title'                  => 'nullable|string|max:500',
                'subtitle'               => 'nullable|string|max:500',
                'slug'                   => 'nullable|string|max:500',
                'content'                => $this->translatableRichTextRules(),
                'excerpt'                => $this->translatableRichTextRules(),
                'writer_notes'           => 'nullable|string|max:500',
                'featured_image'         => 'nullable|string',
                'video_embed'            => 'nullable|string',
                'locale'                 => 'nullable|in:ar,en',
                'read_time'              => 'nullable|integer|min:1',
                'is_breaking'            => 'boolean',
                'is_premium'             => 'boolean',
                'seo_title_en'           => 'nullable|string|max:200',
                'seo_title_ar'           => 'nullable|string|max:200',
                'seo_description_en'     => 'nullable|string|max:400',
                'seo_description_ar'     => 'nullable|string|max:400',
                'seo_title'              => 'nullable|string|max:200',
                'seo_description'        => 'nullable|string|max:400',
                'status'                 => 'nullable|in:draft,pending',
                'tags'                   => 'nullable|array',
                'tags.*'                 => 'integer|exists:tags,id',
                'secondary_categories'   => 'nullable|array',
                'secondary_categories.*' => 'exists:categories,id',
            ]);

            $this->mapLegacyTranslationInput($data);

            $apiStatus = $data['status'] ?? 'pending';
            unset($data['status']);

            $status = $this->resolveArticleStatus($apiStatus);

            $article = Article::create([
                ...$this->extractArticleBaseAttributes($data),
                'author_id'    => $user->id,
                'status'       => $status,
                'submitted_at' => $status === 'submitted' ? now() : null,
            ]);

            $this->fillArticleTranslations($article, $data);

            if (array_key_exists('tags', $data)) {
                $article->tags()->sync($data['tags']);
            }
            if (! empty($data['secondary_categories'])) {
                $article->secondaryCategories()->sync($data['secondary_categories']);
            }

            return $this->success(
                $this->formatArticleForApi($article->load(['primaryCategory', 'tags'])),
                'Article created successfully.',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to create article.');
        }
    }

    public function update(Request $request, int $articleId): JsonResponse
    {
        try {
            $user    = $request->user();
            $article = Article::find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $isOwner  = $article->author_id === $user->id;
            $isEditor = $user->editor()->exists() || $user->admin()->exists();

            if (! $isOwner && ! $isEditor) {
                return $this->error(null, 'You are not authorized to edit this article.', 403);
            }

            $this->prepareTranslatableRequest($request);

            $data = $request->validate([
                'primary_category_id'    => 'sometimes|exists:categories,id',
                'title_en'               => 'nullable|string|max:500',
                'title_ar'               => 'nullable|string|max:500',
                'subtitle_en'            => 'nullable|string|max:500',
                'subtitle_ar'            => 'nullable|string|max:500',
                'slug_en'                => 'nullable|string|max:500',
                'slug_ar'                => 'nullable|string|max:500',
                'content_en'             => $this->translatableRichTextRules(),
                'content_ar'             => $this->translatableRichTextRules(),
                'excerpt_en'             => $this->translatableRichTextRules(),
                'excerpt_ar'             => $this->translatableRichTextRules(),
                'title'                  => 'nullable|string|max:500',
                'subtitle'               => 'nullable|string|max:500',
                'slug'                   => 'nullable|string|max:500',
                'content'                => $this->translatableRichTextRules(),
                'excerpt'                => $this->translatableRichTextRules(),
                'writer_notes'           => 'nullable|string|max:500',
                'featured_image'         => 'nullable|string',
                'video_embed'            => 'nullable|string',
                'locale'                 => 'nullable|in:ar,en',
                'read_time'              => 'nullable|integer|min:1',
                'is_breaking'            => 'boolean',
                'is_premium'             => 'boolean',
                'seo_title_en'           => 'nullable|string|max:200',
                'seo_title_ar'           => 'nullable|string|max:200',
                'seo_description_en'     => 'nullable|string|max:400',
                'seo_description_ar'     => 'nullable|string|max:400',
                'seo_title'              => 'nullable|string|max:200',
                'seo_description'        => 'nullable|string|max:400',
                'status'                 => 'nullable|in:draft,pending,published,archived',
                'tags'                   => 'nullable|array',
                'tags.*'                 => 'integer|exists:tags,id',
                'secondary_categories'   => 'nullable|array',
                'secondary_categories.*' => 'exists:categories,id',
            ]);

            $this->mapLegacyTranslationInput($data);

            $this->applyStatusFields($data, $article);

            $article->fill($this->extractArticleBaseAttributes($data))->save();
            $this->fillArticleTranslations($article, $data);

            if (isset($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }
            if (isset($data['secondary_categories'])) {
                $article->secondaryCategories()->sync($data['secondary_categories']);
            }

            return $this->success(
                $this->formatArticleForApi($article->load(['primaryCategory', 'tags'])),
                'Article updated successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update article.');
        }
    }

    public function relatedStories(Request $request, int $articleId): JsonResponse
    {
        try {
            $article = Article::published()->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $tagIds       = $article->tags()->pluck('tags.id');
            $secondaryIds = $article->secondaryCategories()->pluck('categories.id');

            $locale = $this->resolveApiLocale($request);

            $related = Article::published()
                ->withTranslation($locale)
                ->with(['author:id,name', 'primaryCategory:id,name,slug', 'tags:id,name,slug'])
                ->where('id', '!=', $article->id)
                ->where(function ($query) use ($article, $tagIds, $secondaryIds) {
                    $query->where('primary_category_id', $article->primary_category_id);

                    if ($secondaryIds->isNotEmpty()) {
                        $query->orWhereHas('secondaryCategories', fn ($q) => $q->whereIn('categories.id', $secondaryIds));
                    }

                    if ($tagIds->isNotEmpty()) {
                        $query->orWhereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds));
                    }
                })
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            return $this->success(
                $this->withIsSavedOnCollection($related, $request)->values(),
                'Related stories retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve related stories.');
        }
    }

    public function trendingTopics(int $articleId): JsonResponse
    {
        try {
            $article = Article::published()->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $topics = Tag::query()
                ->select(['tags.id', 'tags.name', 'tags.slug'])
                ->selectRaw('SUM(articles.views_count) as total_views')
                ->selectRaw('COUNT(DISTINCT articles.id) as article_count')
                ->join('article_tags', 'tags.id', '=', 'article_tags.tag_id')
                ->join('articles', 'article_tags.article_id', '=', 'articles.id')
                ->where('articles.status', 'published')
                ->when($article->primary_category_id, fn ($q) => $q->where('articles.primary_category_id', $article->primary_category_id))
                ->groupBy('tags.id', 'tags.name', 'tags.slug')
                ->orderByDesc('total_views')
                ->limit(10)
                ->get();

            return $this->success($topics, 'Trending topics retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve trending topics.');
        }
    }

    public function nextRead(Request $request, int $articleId): JsonResponse
    {
        try {
            $article = Article::published()->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $locale = $this->resolveApiLocale($request);

            $next = Article::published()
                ->withTranslation($locale)
                ->with(['author:id,name', 'primaryCategory:id,name,slug'])
                ->where('primary_category_id', $article->primary_category_id)
                ->where('id', '!=', $article->id)
                ->where(function ($query) use ($article) {
                    $query->where('published_at', '>', $article->published_at)
                        ->orWhere(function ($q) use ($article) {
                            $q->where('published_at', $article->published_at)
                                ->where('id', '>', $article->id);
                        });
                })
                ->orderBy('published_at')
                ->orderBy('id')
                ->first();

            if (! $next) {
                $next = Article::published()
                    ->withTranslation($locale)
                    ->with(['author:id,name', 'primaryCategory:id,name,slug'])
                    ->where('primary_category_id', $article->primary_category_id)
                    ->where('id', '!=', $article->id)
                    ->orderByDesc('published_at')
                    ->first();
            }

            if (! $next) {
                return $this->error(null, 'No next article found.', 404);
            }

            return $this->success(
                $this->withIsSaved($next, $request),
                'Next read retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve next read.');
        }
    }

    public function destroy(Request $request, int $articleId): JsonResponse
    {
        try {
            $user    = $request->user();
            $article = Article::find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            if ($article->author_id !== $user->id && ! $user->admin()->exists()) {
                return $this->error(null, 'You are not authorized to delete this article.', 403);
            }

            $article->delete();

            return $this->success(null, 'Article deleted successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to delete article.');
        }
    }

    private function resolveArticleStatus(?string $status): string
    {
        return $status === 'draft' ? 'draft' : 'submitted';
    }

    private function applyStatusFields(array &$data, ?Article $article = null): void
    {
        if (! array_key_exists('status', $data)) {
            return;
        }

        if ($data['status'] === 'pending') {
            $data['status'] = 'submitted';

            if (! $article?->submitted_at) {
                $data['submitted_at'] = now();
            }

            return;
        }

        if ($data['status'] === 'draft') {
            $data['status']       = 'draft';
            $data['submitted_at'] = null;
        }
    }

    private function formatArticleForApi(Article $article): Article
    {
        if ($article->status === 'submitted') {
            $article->status = 'pending';
        }

        return $article;
    }

    private function formatArticleDetailForApi(Article $article, Request $request): array
    {
        $locale = $this->resolveApiLocale($request);
        $translation = $article->translate($locale, false) ?? $article->translate($locale);
        $article = $this->formatArticleForApi($article);

        return [
            ...(new ArticleSummaryResource($article))->toArray($request),
            'content'          => $translation?->content,
            'seo_title'        => $translation?->seo_title,
            'seo_description'  => $translation?->seo_description,
            'video_embed'      => $article->video_embed,
            'writer_notes'     => $article->writer_notes,
            'secondary_categories' => $article->relationLoaded('secondaryCategories')
                ? $article->secondaryCategories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                : [],
        ];
    }

    private function extractArticleBaseAttributes(array $data): array
    {
        return collect($data)->only([
            'primary_category_id',
            'writer_notes',
            'featured_image',
            'video_embed',
            'read_time',
            'is_breaking',
            'is_premium',
        ])->all();
    }

    private function fillArticleTranslations(Article $article, array $data): void
    {
        $hasChanges = false;

        foreach ($article->translatedAttributeNames() as $field) {
            foreach (['en', 'ar'] as $locale) {
                $key = "{$field}_{$locale}";
                if (array_key_exists($key, $data)) {
                    $article->setAttribute($key, $data[$key]);
                    $hasChanges = true;
                }
            }
        }

        if ($hasChanges) {
            $this->persistModelTranslations($article);
        }
    }
}
