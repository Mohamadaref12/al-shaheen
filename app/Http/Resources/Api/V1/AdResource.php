<?php

namespace App\Http\Resources\Api\V1;

use App\Support\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'placement'   => $this->placement,
            'image_url'   => ImageStorage::url($this->image_url),
            'link_url'    => $this->link_url,
            'ad_category' => $this->ad_category,
            'is_native'   => (bool) $this->is_native,
            'starts_at'   => $this->starts_at?->toIso8601String(),
            'ends_at'     => $this->ends_at?->toIso8601String(),
        ];
    }
}
