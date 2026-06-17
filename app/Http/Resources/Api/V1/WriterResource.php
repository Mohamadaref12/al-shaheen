<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesImageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class WriterResource extends JsonResource
{
    use ResolvesImageUrls;

    public function toArray(Request $request): array
    {
        $socialLinks = $this->normalizedSocialLinks();

        return [
            'id'                    => $this->id,
            'display_name'          => $this->display_name,
            'bio'                   => $this->bio,
            'profile_photo'         => $this->profile_photo,
            'profile_photo_url'     => $this->imageUrl($this->profile_photo),
            'portfolio_link'        => $this->portfolio_link,
            'experience_level'      => $this->experience_level,
            'languages'             => $this->languages ?? [],
            'editorial_specialties' => $this->editorial_specialties ?? [],
            'location'              => $this->location,
            'social_links'          => $socialLinks,
            'twitter'               => $socialLinks['twitter'],
            'linkedin'              => $socialLinks['linkedin'],
            'is_verified_writer'    => (bool) $this->is_verified_writer,
            'media_affiliation'     => $this->media_affiliation,
            'articles_count'        => $this->whenCounted('articles'),
            'articles'              => ArticleSummaryResource::collection($this->whenLoaded('articles')),
            'articles_meta'         => $this->when(
                $this->relationLoaded('articles') && $this->articles instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator,
                fn () => [
                    'current_page' => $this->articles->currentPage(),
                    'per_page'     => $this->articles->perPage(),
                    'total'        => $this->articles->total(),
                    'last_page'    => $this->articles->lastPage(),
                ]
            ),
            'user'                  => $this->whenLoaded('user', fn () => [
                'id'      => $this->user?->id,
                'name'    => $this->user?->name,
                'country' => $this->user?->country,
            ]),
            'categories'            => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at'            => $this->created_at?->toIso8601String(),
        ];
    }

    protected function normalizedSocialLinks(): array
    {
        $links = collect($this->social_links ?? [])
            ->mapWithKeys(fn ($url, $key) => [strtolower(trim((string) $key)) => $url]);

        return [
            'twitter'  => $this->resolveSocialUrl($links, ['twitter', 'x']),
            'linkedin' => $this->resolveSocialUrl($links, ['linkedin']),
        ];
    }

    protected function resolveSocialUrl(Collection $links, array $keys): ?string
    {
        foreach ($keys as $key) {
            $url = $links->get($key);

            if (filled($url)) {
                return (string) $url;
            }
        }

        return null;
    }
}
