<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingCourseResource extends JsonResource
{
    use ResolvesImageUrls;

    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'title'                    => $this->title,
            'slug'                     => $this->slug,
            'excerpt'                  => $this->excerpt ?? $this->description,
            'description'              => $this->description,
            'level'                    => $this->level,
            'level_label'              => $this->levelLabel(),
            'thumbnail'                => $this->thumbnail,
            'thumbnail_url'            => $this->imageUrl($this->thumbnail),
            'price'                    => $this->price,
            'original_price'           => $this->original_price,
            'currency'                 => $this->currency ?? 'USD',
            'is_discounted'            => $this->isDiscounted(),
            'is_premium'               => (bool) $this->is_premium,
            'duration_weeks'           => $this->duration_weeks,
            'lessons_count'            => $this->whenCounted('lessons'),
            'total_duration_minutes'   => (int) ($this->total_duration_minutes ?? 0),
            'downloadable_files_count' => $this->downloadable_files_count,
            'has_lifetime_access'      => (bool) $this->has_lifetime_access,
            'rating'                   => $this->rating,
            'reviews_count'            => $this->reviews_count,
            'instructor'               => [
                'name'       => $this->instructor_name,
                'label'      => $this->instructor_label,
                'avatar'     => $this->instructor_avatar,
                'avatar_url' => $this->imageUrl($this->instructor_avatar),
            ],
            'category' => $this->whenLoaded('category', fn () => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
                'icon' => $this->category?->icon,
            ]),
        ];
    }

    protected function levelLabel(): string
    {
        return match ($this->level) {
            'beginner'     => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced'     => 'Advanced',
            default        => ucfirst((string) $this->level),
        };
    }

    protected function isDiscounted(): bool
    {
        return $this->original_price !== null
            && $this->price !== null
            && (float) $this->original_price > (float) $this->price;
    }
}
