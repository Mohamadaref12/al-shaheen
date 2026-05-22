<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Event::with('author:id,name');

            if ($request->boolean('upcoming')) {
                $query->where('starts_at', '>=', now());
            }
            if ($request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            $paginator = $query->orderBy('starts_at')->paginate($request->input('per_page', 15));

            return $this->pagedSuccess(
                $paginator->items(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Events retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve events.');
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            $event = Event::with('author:id,name')->where('slug', $slug)->first();

            if (! $event) {
                return $this->error(null, 'Event not found.', 404);
            }

            return $this->success($event, 'Event retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve event.');
        }
    }
}
