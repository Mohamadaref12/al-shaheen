<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class CourseCategoryDetailResource extends CourseCategoryResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'is_active' => (bool) $this->is_active,
            'courses'   => TrainingCourseResource::collection(
                $this->whenLoaded('courses')
            ),
        ];
    }
}
