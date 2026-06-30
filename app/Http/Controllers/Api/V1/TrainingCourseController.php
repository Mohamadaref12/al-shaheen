<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseCategoryResource;
use App\Http\Resources\Api\V1\TrainingCourseDetailResource;
use App\Http\Resources\Api\V1\TrainingCourseResource;
use App\Http\Resources\Api\V1\TrainingLessonDetailResource;
use App\Http\Resources\Api\V1\TrainingLessonResource;
use App\Models\CourseCategory;
use App\Models\TrainingLesson;
use App\Traits\QueriesTrainingCourses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TrainingCourseController extends Controller
{
    use QueriesTrainingCourses;

    public function filters(): JsonResponse
    {
        try {
            $categories = CourseCategory::query()
                ->where('is_active', true)
                ->withCount(['courses' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'icon', 'sort_order']);

            return $this->success([
                'categories' => CourseCategoryResource::collection($categories),
                'levels'     => [
                    ['value' => 'beginner',     'label' => 'Beginner'],
                    ['value' => 'intermediate', 'label' => 'Intermediate'],
                    ['value' => 'advanced',     'label' => 'Advanced'],
                ],
                'sort' => [
                    ['value' => 'default',    'label' => 'Default'],
                    ['value' => 'newest',     'label' => 'Newest'],
                    ['value' => 'price_asc',  'label' => 'Price: Low to High'],
                    ['value' => 'price_desc', 'label' => 'Price: High to Low'],
                    ['value' => 'rating',     'label' => 'Top Rated'],
                ],
            ], 'Training filters retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve training filters.');
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'category' => 'nullable|string',
                'level'    => 'nullable|in:beginner,intermediate,advanced',
                'premium'  => 'nullable|boolean',
                'search'   => 'nullable|string|max:255',
                'sort'     => 'nullable|in:default,newest,price_asc,price_desc,rating',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $query = $this->applyTrainingCourseFilters(
                $this->trainingCourseBaseQuery(),
                $request
            );

            $paginator = $query->paginate($request->input('per_page', 12));

            return $this->pagedSuccess(
                TrainingCourseResource::collection($paginator->items())->resolve(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Courses retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve courses.');
        }
    }

    public function show(string $course): JsonResponse
    {
        try {
            $record = $this->findActiveTrainingCourse($course, [
                'lessons' => fn ($q) => $q->orderBy('sort_order'),
            ]);

            if (! $record) {
                return $this->error(null, 'Course not found.', 404);
            }

            $record->setRelation('relatedCourses', $this->relatedCoursesFor($record));

            return $this->success(
                new TrainingCourseDetailResource($record),
                'Course retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve course.');
        }
    }

    public function lessons(string $course): JsonResponse
    {
        try {
            $record = $this->findActiveTrainingCourse($course);

            if (! $record) {
                return $this->error(null, 'Course not found.', 404);
            }

            $lessons = $record->lessons()->orderBy('sort_order')->get();

            return $this->success([
                'course' => [
                    'id'    => $record->id,
                    'title' => $record->title,
                    'slug'  => $record->slug,
                ],
                'lessons' => TrainingLessonResource::collection($lessons),
                'meta'    => [
                    'lessons_count'         => $lessons->count(),
                    'total_duration_minutes' => (int) $lessons->sum('duration_minutes'),
                ],
            ], 'Course lessons retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve course lessons.');
        }
    }

    public function related(string $course, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:12',
            ]);

            $record = $this->findActiveTrainingCourse($course);

            if (! $record) {
                return $this->error(null, 'Course not found.', 404);
            }

            $limit = min((int) $request->input('limit', 3), 12);
            $related = $this->relatedCoursesFor($record, $limit);

            return $this->success(
                TrainingCourseResource::collection($related),
                'Related courses retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve related courses.');
        }
    }

    public function showLesson(int $lessonId): JsonResponse
    {
        try {
            $lesson = TrainingLesson::query()
                ->with(['course' => fn ($q) => $q
                    ->with('category')
                    ->withCount('lessons')
                    ->where('is_active', true)])
                ->whereHas('course', fn ($q) => $q->where('is_active', true))
                ->find($lessonId);

            if (! $lesson || ! $lesson->course) {
                return $this->error(null, 'Lesson not found.', 404);
            }

            return $this->success(
                new TrainingLessonDetailResource($lesson),
                'Lesson retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve lesson.');
        }
    }
}
