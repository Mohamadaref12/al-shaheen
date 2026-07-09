<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Traits\AppliesTranslatableLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ReportController extends Controller
{
    use AppliesTranslatableLocale;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Report::with(array_merge(
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
            if ($request->boolean('premium')) {
                $query->where('is_premium', true);
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
                'Reports retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve reports.');
        }
    }

    public function show(Request $request, int $reportId): JsonResponse
    {
        try {
            $report = Report::with(array_merge(
                ['author:id,name'],
                $this->localizedCategoryEagerLoads($request, 'category')
            ))
                ->where('id', $reportId)
                ->where('status', 'published')
                ->first();

            if (! $report) {
                return $this->error(null, 'Report not found.', 404);
            }

            $report->increment('views_count');

            return $this->success($report, 'Report retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve report.');
        }
    }
}
