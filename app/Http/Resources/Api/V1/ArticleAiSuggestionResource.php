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
            'kind'              => $this->kind ?? 'improvement',
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
            'apply_hint'        => $this->kind === 'translation'
                ? ($this->article_id
                    ? 'Review suggestions and apply via PUT /articles/{id} with title_{locale}, content_{locale}, etc. Nothing is auto-applied.'
                    : 'Review suggestions, fill the create form (AR + EN), then POST /articles. Nothing is auto-applied.')
                : 'Review suggestions and apply manually via article update.',
        ];
    }
}
