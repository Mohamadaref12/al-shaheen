<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleSummaryResource;
use App\Models\Category;
use App\Http\Resources\Api\V1\HighPerformingWriterResource;
use App\Traits\FetchesHighPerformingWriters;
use App\Traits\FetchesPublishedArticles;
use App\Traits\MarksSavedArticles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
{
    use FetchesHighPerformingWriters;
    use FetchesPublishedArticles;
    use MarksSavedArticles;
    // ─── Primary Categories (parent_id IS NULL) ────────────────────────────

    public function primaryIndex(): JsonResponse
    {
        try {
            $categories = Category::withCount('children')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $this->success($categories, 'Primary categories retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary categories.');
        }
    }

    public function primarySecondaryIndex(int $categoryId): JsonResponse
    {
        try {
            $primary = Category::whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first();

            if (! $primary) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            $secondary = Category::where('parent_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $this->success([
                'primary'   => $primary,
                'secondary' => $secondary,
            ], 'Secondary categories retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve secondary categories.');
        }
    }

    public function primaryFilters(int $categoryId): JsonResponse
    {
        try {
            $category = Category::query()
                ->whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'name', 'slug', 'description', 'image']);

            if (! $category) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            $secondaryCategories = Category::query()
                ->where('parent_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'parent_id', 'name', 'slug', 'image']);

            return $this->success([
                'category'              => $category,
                'secondary_categories'  => $secondaryCategories,
                ...$this->categoryFilterOptions(),
            ], 'Primary category filters retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category filters.');
        }
    }

    public function primaryArticles(Request $request, int $categoryId): JsonResponse
    {
        try {
            $request->validate($this->categoryListingValidationRules());

            $category = Category::query()
                ->whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'name', 'slug', 'image']);

            if (! $category) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            if ($request->filled('secondary')) {
                $secondary = Category::query()
                    ->where('id', $request->input('secondary'))
                    ->where('parent_id', $categoryId)
                    ->where('is_active', true)
                    ->exists();

                if (! $secondary) {
                    return $this->error(null, 'Secondary category not found under this primary category.', 404);
                }
            }

            $query = $this->publishedArticleQuery($request, $categoryId, 'primary');
            $this->applyArticleListingFilters($query, $request);

            $paginator = $this->withIsSavedOnPaginator(
                $query->paginate((int) $request->input('per_page', 15)),
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
                'Primary category articles retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category articles.');
        }
    }

    public function primaryTrending(Request $request, int $categoryId): JsonResponse
    {
        try {
            $request->validate([
                'secondary' => 'nullable|integer|exists:categories,id',
                'locale'    => 'nullable|in:ar,en',
                'limit'     => 'nullable|integer|min:1|max:20',
            ]);

            $category = Category::query()
                ->whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'name', 'slug', 'image']);

            if (! $category) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            $secondary = null;

            if ($request->filled('secondary')) {
                $secondary = Category::query()
                    ->where('id', $request->input('secondary'))
                    ->where('parent_id', $categoryId)
                    ->where('is_active', true)
                    ->first(['id', 'parent_id', 'name', 'slug', 'image']);

                if (! $secondary) {
                    return $this->error(null, 'Secondary category not found under this primary category.', 404);
                }
            }

            $articles = $this->withIsSavedOnCollection(
                $this->fetchTrendingArticles($request, $categoryId, 'primary'),
                $request
            );

            return $this->success([
                'category'  => $category,
                'secondary' => $secondary,
                'articles'  => $articles->values(),
            ], 'Primary category trending articles retrieved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category trending articles.');
        }
    }

    public function primaryEditorPicks(Request $request, int $categoryId): JsonResponse
    {
        try {
            $request->validate([
                'secondary' => 'nullable|integer|exists:categories,id',
                'locale'    => 'nullable|in:ar,en',
                'limit'     => 'nullable|integer|min:1|max:20',
            ]);

            $category = Category::query()
                ->whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'name', 'slug', 'image']);

            if (! $category) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            $secondary = null;

            if ($request->filled('secondary')) {
                $secondary = Category::query()
                    ->where('id', $request->input('secondary'))
                    ->where('parent_id', $categoryId)
                    ->where('is_active', true)
                    ->first(['id', 'parent_id', 'name', 'slug', 'image']);

                if (! $secondary) {
                    return $this->error(null, 'Secondary category not found under this primary category.', 404);
                }
            }

            $articles = $this->withIsSavedOnCollection(
                $this->fetchEditorPicks($request, $categoryId, 'primary'),
                $request
            );

            return $this->success([
                'category'  => $category,
                'secondary' => $secondary,
                'articles'  => $articles->values(),
            ], 'Primary category editor picks retrieved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category editor picks.');
        }
    }

    public function primaryWriters(Request $request, int $categoryId): JsonResponse
    {
        try {
            $request->validate([
                'secondary' => 'nullable|integer|exists:categories,id',
                'limit'     => 'nullable|integer|min:1|max:20',
            ]);

            $category = Category::query()
                ->whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'name', 'slug', 'image']);

            if (! $category) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            $secondary = null;
            $secondaryId = null;

            if ($request->filled('secondary')) {
                $secondary = Category::query()
                    ->where('id', $request->input('secondary'))
                    ->where('parent_id', $categoryId)
                    ->where('is_active', true)
                    ->first(['id', 'parent_id', 'name', 'slug', 'image']);

                if (! $secondary) {
                    return $this->error(null, 'Secondary category not found under this primary category.', 404);
                }

                $secondaryId = $secondary->id;
            }

            $writers = $this->fetchHighPerformingWriters($request, $categoryId, $secondaryId);

            return $this->success([
                'category'  => $category,
                'secondary' => $secondary,
                'writers'   => HighPerformingWriterResource::collection($writers)->resolve(),
            ], 'Primary category high-performing writers retrieved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category writers.');
        }
    }

    public function primaryShow(Request $request, int $categoryId): JsonResponse
    {
        try {
            $category = Category::with([
                'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                'articles' => fn ($q) => $q
                    ->with('author:id,name')
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->limit(20),
            ])
                ->whereNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first();

            if (! $category) {
                return $this->error(null, 'Primary category not found.', 404);
            }

            $this->withIsSavedOnCollection($category->articles, $request);

            return $this->success($category, 'Primary category retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category.');
        }
    }

    // ─── Secondary Categories / Subcategories (parent_id IS NOT NULL) ──────

    public function secondaryIndex(): JsonResponse
    {
        try {
            $categories = Category::with('parent:id,name,slug')
                ->whereNotNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $this->success($categories, 'Secondary categories retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve secondary categories.');
        }
    }

    public function secondaryFilters(int $categoryId): JsonResponse
    {
        try {
            $category = Category::query()
                ->with('parent:id,name,slug')
                ->whereNotNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'parent_id', 'name', 'slug', 'description', 'image']);

            if (! $category) {
                return $this->error(null, 'Secondary category not found.', 404);
            }

            return $this->success([
                'category' => $category,
                ...$this->categoryFilterOptions(),
            ], 'Secondary category filters retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve secondary category filters.');
        }
    }

    public function secondaryArticles(Request $request, int $categoryId): JsonResponse
    {
        try {
            $request->validate(collect($this->categoryListingValidationRules())
                ->except(['secondary'])
                ->all());

            $category = Category::query()
                ->whereNotNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first(['id', 'name', 'slug', 'image']);

            if (! $category) {
                return $this->error(null, 'Secondary category not found.', 404);
            }

            $query = $this->publishedArticleQuery($request);
            $this->applySecondaryCategoryFilter($query, $categoryId);
            $this->applyArticleListingFilters($query, $request);

            $paginator = $this->withIsSavedOnPaginator(
                $query->paginate((int) $request->input('per_page', 15)),
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
                'Secondary category articles retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve secondary category articles.');
        }
    }

    public function secondaryShow(Request $request, int $categoryId): JsonResponse
    {
        try {
            $category = Category::with([
                'parent:id,name,slug',
                'secondaryArticles' => fn ($q) => $q
                    ->with('author:id,name')
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->limit(20),
            ])
                ->whereNotNull('parent_id')
                ->where('id', $categoryId)
                ->where('is_active', true)
                ->first();

            if (! $category) {
                return $this->error(null, 'Secondary category not found.', 404);
            }

            $this->withIsSavedOnCollection($category->secondaryArticles, $request);

            return $this->success($category, 'Secondary category retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve secondary category.');
        }
    }
}
