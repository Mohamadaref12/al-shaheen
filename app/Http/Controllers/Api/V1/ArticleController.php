<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Article::with(['author:id,name', 'primaryCategory:id,name,slug', 'tags:id,name,slug'])
                ->where('status', 'published');

            if ($request->filled('category')) {
                $query->where('primary_category_id', $request->input('category'));
            }
            if ($request->filled('tag')) {
                $search = $request->input('tag');
                $query->whereHas('tags', fn ($q) => $q->where('slug', $search));
            }
            if ($request->filled('locale')) {
                $query->where('locale', $request->input('locale'));
            }
            if ($request->boolean('breaking')) {
                $query->where('is_breaking', true);
            }
            if ($request->boolean('premium')) {
                $query->where('is_premium', true);
            }
            if ($request->filled('search')) {
                $term = $request->input('search');
                $query->where(fn ($q) => $q
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"));
            }

            match ($request->input('sort', 'latest')) {
                'views'  => $query->orderByDesc('views_count'),
                'oldest' => $query->orderBy('published_at'),
                default  => $query->orderByDesc('published_at'),
            };

            $paginator = $query->paginate($request->input('per_page', 15));

            return $this->pagedSuccess(
                $paginator->items(),
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

    public function show(int $articleId): JsonResponse
    {
        try {
            $article = Article::with([
                'author:id,name',
                'primaryCategory:id,name,slug',
                'secondaryCategories:id,name,slug',
                'tags:id,name,slug',
            ])
                ->where('id', $articleId)
                ->where('status', 'published')
                ->first();

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $article->increment('views_count');

            return $this->success($article, 'Article retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve article.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user->writer()->exists() && ! $user->editor()->exists() && ! $user->admin()->exists()) {
                return $this->error(null, 'You are not authorized to publish articles.', 403);
            }

            $data = $request->validate([
                'primary_category_id'    => 'required|exists:categories,id',
                'title'                  => 'required|string|max:500',
                'subtitle'               => 'nullable|string|max:500',
                'slug'                   => 'required|string|unique:articles,slug',
                'content'                => 'required|string',
                'excerpt'                => 'nullable|string|max:1000',
                'featured_image'         => 'nullable|string',
                'video_embed'            => 'nullable|string',
                'locale'                 => 'nullable|in:ar,en',
                'read_time'              => 'nullable|integer|min:1',
                'is_breaking'            => 'boolean',
                'is_premium'             => 'boolean',
                'seo_title'              => 'nullable|string|max:200',
                'seo_description'        => 'nullable|string|max:400',
                'status'                 => 'nullable|in:draft,pending',
                'tags'                   => 'nullable|array',
                'tags.*'                 => 'exists:tags,id',
                'secondary_categories'   => 'nullable|array',
                'secondary_categories.*' => 'exists:categories,id',
            ]);

            $article = Article::create([
                ...$data,
                'author_id'    => $user->id,
                'status'       => $data['status'] ?? 'draft',
                'submitted_at' => now(),
            ]);

            if (! empty($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }
            if (! empty($data['secondary_categories'])) {
                $article->secondaryCategories()->sync($data['secondary_categories']);
            }

            return $this->success(
                $article->load(['primaryCategory', 'tags']),
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

            $data = $request->validate([
                'primary_category_id'    => 'sometimes|exists:categories,id',
                'title'                  => 'sometimes|string|max:500',
                'subtitle'               => 'nullable|string|max:500',
                'slug'                   => 'sometimes|string|unique:articles,slug,' . $articleId,
                'content'                => 'sometimes|string',
                'excerpt'                => 'nullable|string|max:1000',
                'featured_image'         => 'nullable|string',
                'video_embed'            => 'nullable|string',
                'locale'                 => 'nullable|in:ar,en',
                'read_time'              => 'nullable|integer|min:1',
                'is_breaking'            => 'boolean',
                'is_premium'             => 'boolean',
                'seo_title'              => 'nullable|string|max:200',
                'seo_description'        => 'nullable|string|max:400',
                'status'                 => 'nullable|in:draft,pending,published,archived',
                'tags'                   => 'nullable|array',
                'tags.*'                 => 'exists:tags,id',
                'secondary_categories'   => 'nullable|array',
                'secondary_categories.*' => 'exists:categories,id',
            ]);

            $article->fill($data)->save();

            if (isset($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }
            if (isset($data['secondary_categories'])) {
                $article->secondaryCategories()->sync($data['secondary_categories']);
            }

            return $this->success(
                $article->load(['primaryCategory', 'tags']),
                'Article updated successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update article.');
        }
    }

    public function relatedStories(int $articleId): JsonResponse
    {
        try {
            $article = Article::published()->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $tagIds       = $article->tags()->pluck('tags.id');
            $secondaryIds = $article->secondaryCategories()->pluck('categories.id');

            $related = Article::published()
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
                ->select([
                    'id', 'author_id', 'primary_category_id', 'title', 'subtitle', 'slug',
                    'excerpt', 'featured_image', 'locale', 'read_time', 'views_count', 'published_at',
                ])
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            return $this->success($related, 'Related stories retrieved successfully.');
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

    public function nextRead(int $articleId): JsonResponse
    {
        try {
            $article = Article::published()->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            $next = Article::published()
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
                ->select([
                    'id', 'author_id', 'primary_category_id', 'title', 'subtitle', 'slug',
                    'excerpt', 'featured_image', 'locale', 'read_time', 'views_count', 'published_at',
                ])
                ->orderBy('published_at')
                ->orderBy('id')
                ->first();

            if (! $next) {
                $next = Article::published()
                    ->with(['author:id,name', 'primaryCategory:id,name,slug'])
                    ->where('primary_category_id', $article->primary_category_id)
                    ->where('id', '!=', $article->id)
                    ->select([
                        'id', 'author_id', 'primary_category_id', 'title', 'subtitle', 'slug',
                        'excerpt', 'featured_image', 'locale', 'read_time', 'views_count', 'published_at',
                    ])
                    ->orderByDesc('published_at')
                    ->first();
            }

            if (! $next) {
                return $this->error(null, 'No next article found.', 404);
            }

            return $this->success($next, 'Next read retrieved successfully.');
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
}
