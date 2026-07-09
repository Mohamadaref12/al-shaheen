<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Traits\AppliesTranslatableLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InterviewController extends Controller
{
    use AppliesTranslatableLocale;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Interview::with(array_merge(
                ['author:id,name'],
                $this->localizedCategoryEagerLoads($request, 'category')
            ))
                ->where('status', 'published');

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
                'Interviews retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve interviews.');
        }
    }

    public function show(Request $request, int $interviewId): JsonResponse
    {
        try {
            $interview = Interview::with(array_merge(
                ['author:id,name'],
                $this->localizedCategoryEagerLoads($request, 'category')
            ))
                ->where('id', $interviewId)
                ->where('status', 'published')
                ->first();

            if (! $interview) {
                return $this->error(null, 'Interview not found.', 404);
            }

            $interview->increment('views_count');

            return $this->success($interview, 'Interview retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve interview.');
        }
    }
}
