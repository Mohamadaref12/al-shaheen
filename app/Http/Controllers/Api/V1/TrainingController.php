<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TrainingCourse;
use App\Models\UserCourseProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TrainingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TrainingCourse::withCount('lessons')->where('is_active', true);

            if ($request->filled('category')) {
                $query->where('category', $request->input('category'));
            }
            if ($request->filled('level')) {
                $query->where('level', $request->input('level'));
            }
            if ($request->boolean('premium')) {
                $query->where('is_premium', true);
            }

            $courses = $query->orderBy('sort_order')->get();

            return $this->success($courses, 'Courses retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve courses.');
        }
    }

    public function show(int $courseId): JsonResponse
    {
        try {
            $course = TrainingCourse::with([
                'lessons' => fn ($q) => $q->orderBy('sort_order'),
            ])
                ->where('id', $courseId)
                ->where('is_active', true)
                ->first();

            if (! $course) {
                return $this->error(null, 'Course not found.', 404);
            }

            return $this->success($course, 'Course retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve course.');
        }
    }

    public function markProgress(Request $request, int $courseId, int $lessonId): JsonResponse
    {
        try {
            $data = $request->validate([
                'is_completed' => 'required|boolean',
            ]);

            $progress = UserCourseProgress::updateOrCreate(
                [
                    'user_id'   => $request->user()->id,
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                ],
                [
                    'is_completed' => $data['is_completed'],
                    'completed_at' => $data['is_completed'] ? now() : null,
                ]
            );

            return $this->success($progress, 'Progress updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update progress.');
        }
    }
}
