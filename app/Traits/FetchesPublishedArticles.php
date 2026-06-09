<?php

namespace App\Traits;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FetchesPublishedArticles
{
    protected function publishedArticleQuery(
        Request $request,
        ?int $categoryId = null,
        ?string $categoryScope = null
    ): Builder {
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

        if ($categoryId && $categoryScope === 'primary') {
            $this->applyPrimaryCategoryFilter($query, $request, $categoryId);
        } elseif ($request->filled('category')) {
            $query->where('primary_category_id', $request->input('category'));
        }

        return $query;
    }

    protected function trendingArticleLimit(Request $request): int
    {
        return min(max((int) $request->input('limit', 6), 1), 20);
    }

    protected function fetchTrendingArticles(
        Request $request,
        ?int $categoryId = null,
        ?string $categoryScope = null
    ) {
        return $this->publishedArticleQuery($request, $categoryId, $categoryScope)
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit($this->trendingArticleLimit($request))
            ->get();
    }

    protected function applyPrimaryCategoryFilter(Builder $query, Request $request, int $primaryCategoryId): void
    {
        if ($request->filled('secondary')) {
            $secondaryId = (int) $request->input('secondary');

            $query->whereHas(
                'secondaryCategories',
                fn ($q) => $q
                    ->where('categories.id', $secondaryId)
                    ->where('categories.parent_id', $primaryCategoryId)
            );

            return;
        }

        $secondaryIds = Category::query()
            ->where('parent_id', $primaryCategoryId)
            ->where('is_active', true)
            ->pluck('id');

        $query->where(function ($q) use ($primaryCategoryId, $secondaryIds): void {
            $q->where('primary_category_id', $primaryCategoryId);

            if ($secondaryIds->isNotEmpty()) {
                $q->orWhereHas(
                    'secondaryCategories',
                    fn ($sq) => $sq->whereIn('categories.id', $secondaryIds)
                );
            }
        });
    }
}
