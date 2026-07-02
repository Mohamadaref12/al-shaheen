<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsAiSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'news_id'           => $this->news_id,
            'kind'              => $this->kind ?? 'translation',
            'focus'             => $this->focus,
            'locale'            => $this->locale,
            'source_locale'     => $this->source_locale,
            'target_locale'     => $this->target_locale,
            'original_snapshot' => $this->original_snapshot,
            'suggestions'       => $this->suggestions,
            'notes'             => $this->notes ?? [],
            'provider'          => $this->provider,
            'model'             => $this->model,
            'status'            => $this->status,
            'created_at'        => $this->created_at?->toIso8601String(),
            'apply_hint'        => $this->news_id
                ? 'Review suggestions and apply via PUT /news/{id} with title_{locale}, content_{locale}, etc. Nothing is auto-applied.'
                : 'Review suggestions, fill the create form (AR + EN), then POST /news. Nothing is auto-applied.',
        ];
    }
}
