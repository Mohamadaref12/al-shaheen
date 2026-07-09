<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OpinionSummaryResource;
use App\Models\Opinion;
use App\Traits\AppliesTranslatableLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OpinionController extends Controller
{
    use AppliesTranslatableLocale;

    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale'   => 'nullable|in:ar,en',
                'category' => 'nullable|integer|exists:categories,id',
                'premium'  => 'nullable|boolean',
                'sort'     => 'nullable|in:latest,views,oldest',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $locale = $this->resolveApiLocale($request);

            $query = Opinion::published()
                ->withTranslation($locale)
                ->with(array_merge(
                    ['author:id,name'],
                    $this->localizedCategoryEagerLoads($request, 'category')
                ));

            if ($request->filled('category')) {
                $query->where('category_id', $request->input('category'));
            }

            if ($request->filled('locale')) {
                $query->translatedIn($request->input('locale'));
            }

            if ($request->boolean('premium')) {
                $query->where('is_premium', true);
            }

            match ($request->input('sort', 'latest')) {
                'views'  => $query->orderByDesc('views_count')->orderByDesc('published_at'),
                'oldest' => $query->orderBy('published_at'),
                default  => $query->orderByDesc('published_at'),
            };

            $paginator = $query->paginate($request->input('per_page', 15));

            return $this->pagedSuccess(
                OpinionSummaryResource::collection($paginator->items())->resolve(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Opinions retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve opinions.');
        }
    }

    public function show(Request $request, int $opinionId): JsonResponse
    {
        try {
            $locale = $this->resolveApiLocale($request);

            $opinion = Opinion::published()
                ->withTranslation($locale)
                ->with(array_merge(
                    ['author:id,name', 'translations'],
                    $this->localizedCategoryEagerLoads($request, 'category')
                ))
                ->where('id', $opinionId)
                ->first();

            if (! $opinion) {
                return $this->error(null, 'Opinion not found.', 404);
            }

            $opinion->increment('views_count');

            $translation = $opinion->translate($locale, false) ?? $opinion->translate($locale);

            return $this->success([
                ...(new OpinionSummaryResource($opinion))->toArray($request),
                'content'         => $translation?->content,
                'seo_title'       => $translation?->seo_title,
                'seo_description' => $translation?->seo_description,
            ], 'Opinion retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve opinion.');
        }
    }
}
