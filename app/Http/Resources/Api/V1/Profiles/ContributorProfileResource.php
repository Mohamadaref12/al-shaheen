<?php

namespace App\Http\Resources\Api\V1\Profiles;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\Concerns\ResolvesImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributorProfileResource extends JsonResource
{
    use ResolvesImageUrls;

    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'bio'               => $this->bio,
            'profile_photo'     => $this->profile_photo,
            'profile_photo_url' => $this->imageUrl($this->profile_photo),
            'portfolio_link'    => $this->portfolio_link,
            'categories'        => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
