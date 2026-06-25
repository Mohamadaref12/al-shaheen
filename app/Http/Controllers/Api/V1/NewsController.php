<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsSummaryResource;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = News::published()
                ->with(['author:id,name', 'category:id,name,slug']);

            if ($request->filled('category')) {
                $query->where('category_id', $request->input('category'));
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

    public function show(int $newsId): JsonResponse
    {
        try {
            $news = News::published()
                ->with(['author:id,name', 'category:id,name,slug'])
                ->where('id', $newsId)
                ->first();

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            $news->increment('views_count');

            return $this->success($news, 'News retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve news.');
        }
    }
}
