<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'parent_id'      => $this->when(isset($this->parent_id), $this->parent_id),
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'image'          => $this->image,
            'sort_order'     => $this->when(isset($this->sort_order), $this->sort_order),
            'locale'         => app()->getLocale(),
            'children_count' => $this->when(isset($this->children_count), $this->children_count),
            'parent'         => CategoryResource::make($this->whenLoaded('parent')),
            'children'       => CategoryResource::collection($this->whenLoaded('children')),
            'articles'       => ArticleSummaryResource::collection($this->whenLoaded('articles')),
            'secondary_articles' => ArticleSummaryResource::collection($this->whenLoaded('secondaryArticles')),
        ];
    }
}
