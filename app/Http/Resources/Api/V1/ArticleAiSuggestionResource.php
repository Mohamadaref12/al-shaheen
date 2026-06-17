<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleAiSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'article_id'        => $this->article_id,
            'focus'             => $this->focus,
            'locale'            => $this->locale,
            'original_snapshot' => $this->original_snapshot,
            'suggestions'       => $this->suggestions,
            'notes'             => $this->notes ?? [],
            'provider'          => $this->provider,
            'model'             => $this->model,
            'status'            => $this->status,
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
