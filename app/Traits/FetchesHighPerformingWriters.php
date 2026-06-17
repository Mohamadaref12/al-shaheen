<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Writer;
use Illuminate\Http\Request;

trait FetchesHighPerformingWriters
{
    protected function writersListLimit(Request $request): int
    {
        return min(max((int) $request->input('limit', 5), 1), 20);
    }

    protected function fetchHighPerformingWriters(
        Request $request,
        ?int $primaryCategoryId = null,
        ?int $secondaryCategoryId = null
    ) {
        $query = Writer::query()
            ->with('user:id,name,country')
            ->where('application_status', 'approved')
            ->withCount('articles');

        if ($secondaryCategoryId && $primaryCategoryId) {
            $query->whereHas(
                'categories',
                fn ($q) => $q->where('categories.id', $secondaryCategoryId)
            );

            $query->withSum([
                'articles as total_views' => fn ($q) => $q->whereHas(
                    'secondaryCategories',
                    fn ($sq) => $sq
                        ->where('categories.id', $secondaryCategoryId)
                        ->where('categories.parent_id', $primaryCategoryId)
                ),
            ], 'views_count');
        } elseif ($primaryCategoryId) {
            $secondaryIds = Category::query()
                ->where('parent_id', $primaryCategoryId)
                ->where('is_active', true)
                ->pluck('id');

            $categoryIds = collect([$primaryCategoryId])->merge($secondaryIds);

            $query->whereHas(
                'categories',
                fn ($q) => $q->whereIn('categories.id', $categoryIds)
            );

            $query->withSum([
                'articles as total_views' => fn ($q) => $q->where(function ($aq) use ($primaryCategoryId, $secondaryIds): void {
                    $aq->where('primary_category_id', $primaryCategoryId);

                    if ($secondaryIds->isNotEmpty()) {
                        $aq->orWhereHas(
                            'secondaryCategories',
                            fn ($sq) => $sq->whereIn('categories.id', $secondaryIds)
                        );
                    }
                }),
            ], 'views_count');
        } else {
            $query->withSum('articles as total_views', 'views_count');
        }

        return $query
            ->orderByDesc('total_views')
            ->orderByDesc('articles_count')
            ->limit($this->writersListLimit($request))
            ->get();
    }
}
