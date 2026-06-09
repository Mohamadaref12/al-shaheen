<?php

namespace App\Http\Resources\Api\V1\Profiles;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\Concerns\ResolvesImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WriterProfileResource extends JsonResource
{
    use ResolvesImageUrls;

    public function toArray(Request $request): array
    {
        return [
            'id'                         => $this->id,
            'display_name'               => $this->display_name,
            'bio'                        => $this->bio,
            'profile_photo'              => $this->profile_photo,
            'profile_photo_url'          => $this->imageUrl($this->profile_photo),
            'portfolio_link'             => $this->portfolio_link,
            'experience_level'           => $this->experience_level,
            'languages'                  => $this->languages,
            'editorial_specialties'      => $this->editorial_specialties,
            'location'                   => $this->location,
            'social_links'               => $this->social_links,
            'is_verified_writer'         => (bool) $this->is_verified_writer,
            'id_verification_file'       => $this->id_verification_file,
            'id_verification_file_url'   => $this->imageUrl($this->id_verification_file),
            'media_affiliation'          => $this->media_affiliation,
            'sample_publications'        => $this->sample_publications,
            'application_status'         => $this->application_status,
            'reviewer_notes'             => $this->reviewer_notes,
            'categories'                 => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at'                 => $this->created_at?->toIso8601String(),
            'updated_at'                 => $this->updated_at?->toIso8601String(),
        ];
    }
}
