<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class TrainingLessonDetailResource extends TrainingLessonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'course' => $this->whenLoaded('course', fn () => new TrainingCourseResource($this->course)),
        ];
    }
}
