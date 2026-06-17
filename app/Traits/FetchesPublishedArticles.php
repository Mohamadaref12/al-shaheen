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

    protected function fetchEditorPicks(
        Request $request,
        ?int $categoryId = null,
        ?string $categoryScope = null
    ) {
        return $this->publishedArticleQuery($request, $categoryId, $categoryScope)
            ->where('is_editor_pick', true)
            ->orderBy('editor_pick_order')
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

    protected function applySecondaryCategoryFilter(Builder $query, int $secondaryCategoryId): void
    {
        $query->whereHas(
            'secondaryCategories',
            fn ($q) => $q->where('categories.id', $secondaryCategoryId)
        );
    }

    protected function applyArticleListingFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('format') && $request->input('format') !== 'all') {
            match ($request->input('format')) {
                'breaking' => $query->where('is_breaking', true),
                'premium'  => $query->where('is_premium', true),
                'video'    => $query->whereNotNull('video_embed')->where('video_embed', '!=', ''),
                default    => null,
            };
        } else {
            if ($request->boolean('breaking')) {
                $query->where('is_breaking', true);
            }

            if ($request->boolean('premium')) {
                $query->where('is_premium', true);
            }
        }

        if ($request->filled('date_range') && $request->input('date_range') !== 'all') {
            match ($request->input('date_range')) {
                'today' => $query->whereDate('published_at', today()),
                'week'  => $query->where('published_at', '>=', now()->startOfWeek()),
                'month' => $query->where('published_at', '>=', now()->startOfMonth()),
                default => null,
            };
        }

        if ($request->filled('from_date')) {
            $query->whereDate('published_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('published_at', '<=', $request->input('to_date'));
        }

        match ($request->input('sort', 'latest')) {
            'views', 'trending' => $query->orderByDesc('views_count')->orderByDesc('published_at'),
            'oldest'            => $query->orderBy('published_at'),
            default             => $query->orderByDesc('published_at'),
        };

        return $query;
    }

    protected function categoryListingValidationRules(): array
    {
        return [
            'secondary'   => 'nullable|integer|exists:categories,id',
            'locale'      => 'nullable|in:ar,en',
            'sort'        => 'nullable|in:latest,views,trending,oldest',
            'format'      => 'nullable|in:all,breaking,premium,video',
            'date_range'  => 'nullable|in:all,today,week,month',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
            'breaking'    => 'nullable|boolean',
            'premium'     => 'nullable|boolean',
            'per_page'    => 'nullable|integer|min:1|max:50',
            'page'        => 'nullable|integer|min:1',
        ];
    }

    protected function categoryFilterOptions(): array
    {
        return [
            'locales' => [
                ['value' => 'ar', 'label' => 'Arabic'],
                ['value' => 'en', 'label' => 'English'],
            ],
            'sort' => [
                ['value' => 'latest', 'label' => 'Latest'],
                ['value' => 'views', 'label' => 'Most Read'],
                ['value' => 'trending', 'label' => 'Trending'],
                ['value' => 'oldest', 'label' => 'Oldest'],
            ],
            'formats' => [
                ['value' => 'all', 'label' => 'All Content'],
                ['value' => 'breaking', 'label' => 'Breaking'],
                ['value' => 'premium', 'label' => 'Premium'],
                ['value' => 'video', 'label' => 'Video'],
            ],
            'date_ranges' => [
                ['value' => 'all', 'label' => 'All Time'],
                ['value' => 'today', 'label' => 'Today'],
                ['value' => 'week', 'label' => 'This Week'],
                ['value' => 'month', 'label' => 'This Month'],
            ],
        ];
    }
}
