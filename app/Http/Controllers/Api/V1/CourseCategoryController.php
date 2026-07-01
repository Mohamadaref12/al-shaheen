<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseCategoryDetailResource;
use App\Http\Resources\Api\V1\CourseCategoryResource;
use App\Traits\AppliesTranslatableLocale;
use App\Traits\QueriesTrainingCourses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CourseCategoryController extends Controller
{
    use AppliesTranslatableLocale;
    use QueriesTrainingCourses;

    public function index(Request $request): JsonResponse
    {
        try {
            $this->resolveApiLocale($request);

            $categories = $this->applyTranslationLocale(
                \App\Models\CourseCategory::query()
                    ->withCount(['courses' => fn ($q) => $q->where('is_active', true)]),
                $request
            )
                ->orderBy('sort_order')
                ->get();

            return $this->success(
                CourseCategoryResource::collection($categories),
                'Course categories retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve course categories.');
        }
    }

    public function show(Request $request, string $category): JsonResponse
    {
        try {
            $locale = $this->resolveApiLocale($request);
            $record = $this->findActiveCourseCategory($category, $locale);

            if (! $record) {
                return $this->error(null, 'Course category not found.', 404);
            }

            $record->loadCount(['courses' => fn ($q) => $q->where('is_active', true)]);
            $record->load([
                'courses' => fn ($q) => $q
                    ->with(['category' => fn ($cq) => $cq->withTranslation($locale)])
                    ->withCount('lessons')
                    ->withSum('lessons as total_duration_minutes', 'duration_minutes')
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ]);

            return $this->success(
                new CourseCategoryDetailResource($record),
                'Course category retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve course category.');
        }
    }
}
