<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Throwable;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = Category::with('children')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $this->success($categories, 'Categories retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve categories.');
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $category = Category::with([
                'children',
                'articles' => fn ($q) => $q
                    ->with('author:id,name')
                    ->where('status', 'published')
                    ->orderByDesc('published_at')
                    ->limit(20),
            ])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (! $category) {
                return $this->error(null, 'Category not found.', 404);
            }

            return $this->success($category, 'Category retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve category.');
        }
    }
}
