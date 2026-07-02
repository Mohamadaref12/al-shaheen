<?php

namespace App\Services\Ai;

use App\Contracts\ArticleImprovementService;
use App\Services\Ai\Concerns\InteractsWithOpenAi;
use App\Support\AiSettings;

class OpenAiArticleImprovementService implements ArticleImprovementService
{
    use InteractsWithOpenAi;

    public function isAvailable(): bool
    {
        return $this->isConfigured();
    }

    public function improve(array $snapshot, string $focus = 'all'): array
    {
        $parsed = $this->chatJson([
            [
                'role'    => 'system',
                'content' => $this->systemPrompt($focus),
            ],
            [
                'role'    => 'user',
                'content' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            ],
        ], temperature: 0.4);

        return [
            'suggestions' => $parsed['suggestions'] ?? [],
            'notes'       => $parsed['notes'] ?? [],
            'provider'    => 'openai',
            'model'       => AiSettings::model(),
        ];
    }

    protected function systemPrompt(string $focus): string
    {
        return <<<PROMPT
You are an editorial assistant for a news platform. Improve the article fields provided in JSON.

Rules:
- Return ONLY valid JSON with this shape:
  {
    "suggestions": {
      "title": "...",
      "subtitle": "...",
      "excerpt": "...",
      "content": "...",
      "seo_title": "...",
      "seo_description": "..."
    },
    "notes": [
      { "field": "title", "reason": "..." }
    ]
  }
- Keep the same language as the input locale (ar = Arabic, en = English).
- Do NOT invent facts. Improve clarity, grammar, structure, and SEO only.
- Preserve HTML in content if present.
- Focus area: {$focus}
- suggestions must contain improved versions only for fields that were provided and non-empty in the input.
PROMPT;
    }
}
