<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class WriterSpotlightResource extends JsonResource
{
    use ResolvesImageUrls;

    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'display_name'       => $this->display_name,
            'bio'                => $this->bio ? Str::limit(strip_tags((string) $this->bio), 160) : null,
            'profile_photo_url'  => $this->imageUrl($this->profile_photo),
            'is_verified_writer' => (bool) $this->is_verified_writer,
            'articles_count'     => (int) ($this->articles_count ?? 0),
            'total_views'        => (int) ($this->total_views ?? 0),
            'user'               => [
                'id'      => $this->user?->id,
                'name'    => $this->user?->name,
                'country' => $this->user?->country,
            ],
        ];
    }
}
