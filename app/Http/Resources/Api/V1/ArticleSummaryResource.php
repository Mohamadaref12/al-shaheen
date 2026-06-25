<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleSummaryResource extends JsonResource
{
    use ResolvesImageUrls;

    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'subtitle'         => $this->subtitle,
            'slug'             => $this->slug,
            'excerpt'          => $this->excerpt,
            'featured_image'   => $this->featured_image,
            'featured_image_url' => $this->imageUrl($this->featured_image),
            'locale'           => $this->locale,
            'read_time'        => $this->read_time,
            'is_breaking'      => (bool) $this->is_breaking,
            'is_premium'       => (bool) $this->is_premium,
            'views_count'      => $this->views_count,
            'published_at'     => $this->published_at?->toIso8601String(),
            'is_saved'         => (bool) ($this->is_saved ?? false),
            'author'             => $this->whenLoaded('author', fn () => [
                'id'   => $this->author?->id,
                'name' => $this->author?->name,
            ]),
            'primary_category' => $this->whenLoaded('primaryCategory', fn () => [
                'id'   => $this->primaryCategory?->id,
                'name' => $this->primaryCategory?->name,
                'slug' => $this->primaryCategory?->slug,
            ]),
            'tags'             => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => [
                'id'   => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])),
        ];
    }
}
