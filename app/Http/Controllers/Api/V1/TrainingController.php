<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TrainingCourseResource;
use App\Http\Resources\Api\V1\TrainingLessonResource;
use App\Models\CourseEnrollment;
use App\Models\TrainingCourse;
use App\Models\UserCourseProgress;
use App\Traits\QueriesTrainingCourses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TrainingController extends Controller
{
    use QueriesTrainingCourses;

    public function myCourses(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status'   => 'nullable|in:in_progress,completed',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $userId = $request->user()->id;

            $allCourses = $this->buildMyCoursesPayload($userId);

            $perPage = (int) $request->input('per_page', 15);

            if ($request->filled('status')) {
                $items = $allCourses
                    ->filter(function (array $item) use ($request) {
                        $isCompleted = $item['progress']['is_completed'];

                        return $request->input('status') === 'completed'
                            ? $isCompleted
                            : ! $isCompleted;
                    })
                    ->values();

                $page = max(1, (int) $request->input('page', 1));
                $total = $items->count();
                $lastPage = max(1, (int) ceil($total / $perPage));

                return $this->pagedSuccess(
                    $items->forPage($page, $perPage)->values(),
                    [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $total,
                        'last_page'    => $lastPage,
                    ],
                    'My courses retrieved successfully.'
                );
            }

            $page = max(1, (int) $request->input('page', 1));
            $total = $allCourses->count();
            $lastPage = max(1, (int) ceil($total / $perPage));

            return $this->pagedSuccess(
                $allCourses->forPage($page, $perPage)->values(),
                [
                    'current_page' => $page,
                    'per_page'     => $perPage,
                    'total'        => $total,
                    'last_page'    => $lastPage,
                ],
                'My courses retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve my courses.');
        }
    }

    public function enroll(Request $request, string $courseId): JsonResponse
    {
        try {
            $course = $this->findActiveTrainingCourse($courseId);

            if (! $course) {
                return $this->error(null, 'Course not found.', 404);
            }

            $enrollment = CourseEnrollment::firstOrCreate(
                [
                    'user_id'   => $request->user()->id,
                    'course_id' => $course->id,
                ],
                [
                    'enrolled_at' => now(),
                ]
            );

            $message = $enrollment->wasRecentlyCreated
                ? 'Enrolled in course successfully.'
                : 'You are already enrolled in this course.';

            return $this->success([
                'is_enrolled'  => true,
                'enrolled_at'  => $enrollment->enrolled_at?->toIso8601String(),
                'course'       => (new TrainingCourseResource($course))->resolve(),
            ], $message, $enrollment->wasRecentlyCreated ? 201 : 200);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to enroll in course.');
        }
    }

    public function myProgress(Request $request, string $courseId): JsonResponse
    {
        try {
            $course = $this->findActiveTrainingCourse($courseId, [
                'lessons' => fn ($q) => $q->orderBy('sort_order'),
            ]);

            if (! $course) {
                return $this->error(null, 'Course not found.', 404);
            }

            return $this->success(
                $this->buildCourseProgressPayload($request->user()->id, $course),
                'Course progress retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve course progress.');
        }
    }

    public function markProgress(Request $request, int $courseId, int $lessonId): JsonResponse
    {
        try {
            $course = $this->findActiveTrainingCourse((string) $courseId);

            if (! $course) {
                return $this->error(null, 'Course not found.', 404);
            }

            $lesson = $course->lessons()->where('id', $lessonId)->first();

            if (! $lesson) {
                return $this->error(null, 'Lesson not found in this course.', 404);
            }

            $data = $request->validate([
                'is_completed' => 'required|boolean',
            ]);

            $userId = $request->user()->id;

            CourseEnrollment::firstOrCreate(
                [
                    'user_id'   => $userId,
                    'course_id' => $course->id,
                ],
                [
                    'enrolled_at' => now(),
                ]
            );

            $progress = UserCourseProgress::updateOrCreate(
                [
                    'user_id'   => $userId,
                    'course_id' => $course->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'is_completed' => $data['is_completed'],
                    'completed_at' => $data['is_completed'] ? now() : null,
                ]
            );

            $course->load([
                'lessons' => fn ($q) => $q->orderBy('sort_order'),
            ]);

            $courseProgress = $this->buildCourseProgressPayload($userId, $course);
            $myCourses = $this->buildMyCoursesPayload($userId);

            return $this->success([
                'updated_lesson' => [
                    'lesson_id'    => $progress->lesson_id,
                    'course_id'    => $progress->course_id,
                    'is_completed' => $progress->is_completed,
                    'completed_at' => $progress->completed_at?->toIso8601String(),
                ],
                ...$courseProgress,
                'my_courses' => $myCourses,
            ], 'Progress updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update progress.');
        }
    }

    protected function buildCourseProgressPayload(int $userId, TrainingCourse $course): array
    {
        $enrollment = CourseEnrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->first();

        $progressByLesson = UserCourseProgress::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('lesson_id');

        $lessons = $course->lessons->map(function ($lesson) use ($progressByLesson) {
            $progress = $progressByLesson->get($lesson->id);

            return [
                ...(new TrainingLessonResource($lesson))->resolve(),
                'is_completed' => (bool) ($progress?->is_completed ?? false),
                'completed_at' => $progress?->completed_at?->toIso8601String(),
            ];
        });

        $totalLessons = $lessons->count();
        $completedLessons = $lessons->where('is_completed', true)->count();
        $completionPercent = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : 0;

        return [
            'is_enrolled' => $enrollment !== null,
            'enrolled_at' => $enrollment?->enrolled_at?->toIso8601String(),
            'course'      => (new TrainingCourseResource($course))->resolve(),
            'summary'     => [
                'total_lessons'      => $totalLessons,
                'completed_lessons'  => $completedLessons,
                'completion_percent' => $completionPercent,
                'is_completed'       => $totalLessons > 0 && $completedLessons === $totalLessons,
            ],
            'lessons'     => $lessons->values(),
        ];
    }

    protected function buildMyCoursesPayload(int $userId): \Illuminate\Support\Collection
    {
        $completedCounts = UserCourseProgress::query()
            ->where('user_id', $userId)
            ->where('is_completed', true)
            ->selectRaw('course_id, COUNT(*) as completed_count')
            ->groupBy('course_id')
            ->pluck('completed_count', 'course_id');

        return CourseEnrollment::query()
            ->where('user_id', $userId)
            ->whereHas('course', fn ($q) => $q->where('is_active', true))
            ->with(['course' => fn ($q) => $q
                ->with('category')
                ->withCount('lessons')
                ->withSum('lessons as total_duration_minutes', 'duration_minutes')])
            ->latest('enrolled_at')
            ->get()
            ->map(function (CourseEnrollment $enrollment) use ($completedCounts): ?array {
                $course = $enrollment->course;

                if (! $course) {
                    return null;
                }

                $totalLessons = (int) $course->lessons_count;
                $completedLessons = (int) ($completedCounts[$course->id] ?? 0);
                $completionPercent = $totalLessons > 0
                    ? (int) round(($completedLessons / $totalLessons) * 100)
                    : 0;
                $isCompleted = $totalLessons > 0 && $completedLessons >= $totalLessons;

                return [
                    'enrolled_at' => $enrollment->enrolled_at?->toIso8601String(),
                    'course'      => (new TrainingCourseResource($course))->resolve(),
                    'progress'    => [
                        'total_lessons'      => $totalLessons,
                        'completed_lessons'  => $completedLessons,
                        'completion_percent' => $completionPercent,
                        'is_completed'       => $isCompleted,
                    ],
                ];
            })
            ->filter()
            ->values();
    }
}
