<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'course_id'        => $this->course_id,
            'title'            => $this->title,
            'description'      => $this->description,
            'video_url'        => $this->video_url,
            'duration_minutes' => $this->duration_minutes,
            'sort_order'       => $this->sort_order,
            'is_premium'       => (bool) $this->is_premium,
        ];
    }
}
