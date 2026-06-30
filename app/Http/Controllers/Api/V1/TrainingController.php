<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserCourseProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TrainingController extends Controller
{
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
