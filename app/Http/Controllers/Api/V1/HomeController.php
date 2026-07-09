<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HighPerformingWriterResource;
use App\Http\Resources\Api\V1\OpinionSummaryResource;
use App\Models\Opinion;
use App\Models\Category;
use App\Models\Writer;
use App\Traits\AppliesTranslatableLocale;
use App\Traits\FetchesPublishedArticles;
use App\Traits\MarksSavedArticles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class HomeController extends Controller
{
    use AppliesTranslatableLocale;
    use FetchesPublishedArticles;
    use MarksSavedArticles;
    public function breakingNews(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale' => 'nullable|in:ar,en',
                'limit'  => 'nullable|integer|min:1|max:20',
            ]);

            $limit = min((int) $request->input('limit', 10), 20);

            $articles = $this->publishedArticleQuery($request)
                ->where('is_breaking', true)
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();

            return $this->success(
                $this->withIsSavedOnCollection($articles, $request)->values(),
                'Breaking news retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve breaking news.');
        }
    }

    public function topArticles(Request $request): JsonResponse
    {
        try {
            $articles = $this->publishedArticleQuery($request)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();

            return $this->success(
                $this->withIsSavedOnCollection($articles, $request)->values(),
                'Top articles retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve top articles.');
        }
    }

    public function trendingArticle(Request $request): JsonResponse
    {
        try {
            $articles = $this->withIsSavedOnCollection(
                $this->fetchTrendingArticles($request),
                $request
            );

            return $this->success($articles->values(), 'Trending articles retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve trending articles.');
        }
    }

    public function editorPicks(Request $request): JsonResponse
    {
        try {
            $limit = min((int) $request->input('limit', 6), 20);

            $articles = $this->publishedArticleQuery($request)
                ->where('is_editor_pick', true)
                ->orderBy('editor_pick_order')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();

            return $this->success(
                $this->withIsSavedOnCollection($articles, $request)->values(),
                'Editor picks retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve editor picks.');
        }
    }

    public function filters(Request $request): JsonResponse
    {
        try {
            $categories = $this->applyTranslationLocale(Category::query(), $request)
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $this->success([
                'categories' => \App\Http\Resources\Api\V1\CategoryResource::collection($categories),
                'locales'    => [
                    ['value' => 'ar', 'label' => 'Arabic'],
                    ['value' => 'en', 'label' => 'English'],
                ],
                'sort' => [
                    ['value' => 'latest', 'label' => 'Latest'],
                    ['value' => 'views',  'label' => 'Most Read'],
                    ['value' => 'oldest', 'label' => 'Oldest'],
                ],
            ], 'Home filters retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve home filters.');
        }
    }

    public function opinion(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale' => 'nullable|in:ar,en',
                'limit'  => 'nullable|integer|min:1|max:20',
            ]);

            $locale = $this->resolveApiLocale($request);
            $limit  = min((int) $request->input('limit', 3), 20);

            $opinions = Opinion::published()
                ->withTranslation($locale)
                ->with(array_merge(
                    ['author:id,name'],
                    $this->localizedCategoryEagerLoads($request, 'category')
                ))
                ->when($request->filled('locale'), fn ($q) => $q->translatedIn($request->input('locale')))
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();

            return $this->success(
                OpinionSummaryResource::collection($opinions)->resolve(),
                'Opinion widget retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve opinion widget.');
        }
    }

    public function writers(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'category_id' => 'nullable|integer|exists:categories,id',
                'limit'       => 'nullable|integer|min:1|max:20',
            ]);

            $limit      = (int) $request->input('limit', 5);
            $categoryId = $request->input('category_id');

            $query = Writer::query()
                ->with('user:id,name')
                ->where('application_status', 'approved')
                ->withCount('articles');

            if ($categoryId) {
                $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));

                $query->withSum([
                    'articles as total_views' => fn ($q) => $q->where('primary_category_id', $categoryId),
                ], 'views_count');
            } else {
                $query->withSum('articles as total_views', 'views_count');
            }

            $writers = $query
                ->orderByDesc('total_views')
                ->orderByDesc('articles_count')
                ->limit($limit)
                ->get();

            return $this->success(
                HighPerformingWriterResource::collection($writers)->resolve(),
                'High-performance writers retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve writers.');
        }
    }

}
