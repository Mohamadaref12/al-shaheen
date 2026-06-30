<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseCategoryDetailResource;
use App\Http\Resources\Api\V1\CourseCategoryResource;
use App\Models\CourseCategory;
use App\Traits\QueriesTrainingCourses;
use Illuminate\Http\JsonResponse;
use Throwable;

class CourseCategoryController extends Controller
{
    use QueriesTrainingCourses;

    public function index(): JsonResponse
    {
        try {
            $categories = CourseCategory::query()
                ->where('is_active', true)
                ->withCount(['courses' => fn ($q) => $q->where('is_active', true)])
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

    public function show(string $category): JsonResponse
    {
        try {
            $record = $this->findActiveCourseCategory($category);

            if (! $record) {
                return $this->error(null, 'Course category not found.', 404);
            }

            $record->loadCount(['courses' => fn ($q) => $q->where('is_active', true)]);
            $record->load([
                'courses' => fn ($q) => $q
                    ->with('category')
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
