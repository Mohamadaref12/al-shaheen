<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class HomeController extends Controller
{
    public function topArticles(Request $request): JsonResponse
    {
        try {
            $articles = $this->baseArticleQuery($request)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();

            return $this->success($articles, 'Top articles retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve top articles.');
        }
    }

    public function trendingArticle(Request $request): JsonResponse
    {
        try {
            $limit = min((int) $request->input('limit', 6), 20);

            $articles = $this->baseArticleQuery($request)
                ->orderByDesc('views_count')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();

            return $this->success($articles, 'Trending articles retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve trending articles.');
        }
    }

    public function editorPicks(Request $request): JsonResponse
    {
        try {
            $limit = min((int) $request->input('limit', 6), 20);

            $articles = $this->baseArticleQuery($request)
                ->where('is_editor_pick', true)
                ->orderBy('editor_pick_order')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();

            return $this->success($articles, 'Editor picks retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve editor picks.');
        }
    }

    public function filters(): JsonResponse
    {
        try {
            $categories = Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'image']);

            return $this->success([
                'categories' => $categories,
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
                ->with('user:id,name,country')
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

            return $this->success($writers, 'High-performance writers retrieved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve writers.');
        }
    }

    private function baseArticleQuery(Request $request)
    {
        $query = Article::published()
            ->with(['author:id,name', 'primaryCategory:id,name,slug', 'tags:id,name,slug'])
            ->select([
                'id', 'author_id', 'primary_category_id', 'title', 'subtitle', 'slug',
                'excerpt', 'featured_image', 'locale', 'read_time', 'is_breaking',
                'is_premium', 'is_editor_pick', 'editor_pick_order', 'views_count', 'published_at',
            ]);

        if ($request->filled('locale')) {
            $query->where('locale', $request->input('locale'));
        }

        if ($request->filled('category')) {
            $query->where('primary_category_id', $request->input('category'));
        }

        return $query;
    }
}
