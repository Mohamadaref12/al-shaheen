<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsSummaryResource;
use App\Models\News;
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
}
