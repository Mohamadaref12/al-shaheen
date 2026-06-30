<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class TrainingCourseDetailResource extends TrainingCourseResource
{
    public function toArray(Request $request): array
    {
        $lessonsCount = $this->lessons_count ?? $this->lessons?->count() ?? 0;

        return [
            ...parent::toArray($request),
            'about_content'     => $this->about_content,
            'about_image'       => $this->about_image,
            'about_image_url'   => $this->imageUrl($this->about_image),
            'video_preview_url' => $this->video_preview_url,
            'learning_outcomes' => $this->normalizeLearningOutcomes(),
            'features'          => $this->features($lessonsCount),
            'lessons'           => TrainingLessonResource::collection(
                $this->whenLoaded('lessons')
            ),
            'related_courses'   => TrainingCourseResource::collection(
                $this->whenLoaded('relatedCourses')
            ),
            'reviews' => [
                'rating'        => $this->rating,
                'reviews_count' => $this->reviews_count,
            ],
            'stats' => [
                'duration_weeks'           => $this->duration_weeks,
                'video_lessons_count'      => $lessonsCount,
                'total_duration_minutes'   => (int) ($this->total_duration_minutes ?? $this->lessons?->sum('duration_minutes') ?? 0),
                'downloadable_files_count' => $this->downloadable_files_count,
                'has_lifetime_access'      => (bool) $this->has_lifetime_access,
            ],
        ];
    }

    private function normalizeLearningOutcomes(): array
    {
        $outcomes = $this->learning_outcomes ?? [];

        return collect($outcomes)
            ->map(fn ($item) => is_array($item) ? ($item['outcome'] ?? reset($item)) : $item)
            ->filter()
            ->values()
            ->all();
    }

    private function features(int $lessonsCount): array
    {
        return [
            [
                'key'       => 'online_videos',
                'label'     => 'Online Videos',
                'available' => $lessonsCount > 0,
            ],
            [
                'key'       => 'editorial_resources',
                'label'     => 'Editorial Resources',
                'available' => $this->downloadable_files_count > 0,
            ],
            [
                'key'       => 'active_community',
                'label'     => 'Active Community',
                'available' => true,
            ],
            [
                'key'       => 'mentor_feedback',
                'label'     => 'Mentor Feedback',
                'available' => filled($this->instructor_name),
            ],
        ];
    }
}
