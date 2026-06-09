<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\FetchesPublishedArticles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
{
    use FetchesPublishedArticles;
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

            $articles = $this->fetchTrendingArticles($request, $categoryId, 'primary');

            return $this->success([
                'category'  => $category,
                'secondary' => $secondary,
                'articles'  => $articles,
            ], 'Primary category trending articles retrieved successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve primary category trending articles.');
        }
    }

    public function primaryShow(int $categoryId): JsonResponse
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

    public function secondaryShow(int $categoryId): JsonResponse
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

            return $this->success($category, 'Secondary category retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve secondary category.');
        }
    }
}
