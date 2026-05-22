<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $tags = Tag::withCount('articles')->orderBy('name')->get();

            return $this->success($tags, 'Tags retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve tags.');
        }
    }

    public function show(string $slug, Request $request): JsonResponse
    {
        try {
            $tag = Tag::where('slug', $slug)->first();

            if (! $tag) {
                return $this->error(null, 'Tag not found.', 404);
            }

            $paginator = $tag->articles()
                ->with(['author:id,name', 'primaryCategory:id,name,slug'])
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->paginate($request->input('per_page', 15));

            return $this->pagedSuccess(
                $paginator->items(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Tag articles retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve tag.');
        }
    }
}
