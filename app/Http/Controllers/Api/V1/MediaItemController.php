<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MediaItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MediaItem::with(['author:id,name', 'category:id,name,slug'])
                ->where('status', 'published');

            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->input('category'));
            }
            if ($request->filled('locale')) {
                $query->where('locale', $request->input('locale'));
            }

            $paginator = $query->orderByDesc('published_at')->paginate($request->input('per_page', 15));

            return $this->pagedSuccess(
                $paginator->items(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Media items retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve media items.');
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $item = MediaItem::with(['author:id,name', 'category:id,name,slug'])
                ->where('slug', $slug)
                ->where('status', 'published')
                ->first();

            if (! $item) {
                return $this->error(null, 'Media item not found.', 404);
            }

            return $this->success($item, 'Media item retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve media item.');
        }
    }
}
