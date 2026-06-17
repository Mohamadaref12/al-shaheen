<?php

namespace App\Services\Ai;

use App\Contracts\ArticleImprovementService;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiArticleImprovementService implements ArticleImprovementService
{
    public function isAvailable(): bool
    {
        return config('ai.enabled') && filled(config('ai.openai.api_key'));
    }

    public function improve(array $snapshot, string $focus = 'all'): array
    {
        $response = Http::baseUrl(rtrim((string) config('ai.openai.base_url'), '/'))
            ->withToken((string) config('ai.openai.api_key'))
            ->timeout((int) config('ai.openai.timeout', 60))
            ->post('/chat/completions', [
                'model'           => config('ai.openai.model', 'gpt-4o-mini'),
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => $this->systemPrompt($focus),
                    ],
                    [
                        'role'    => 'user',
                        'content' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                    ],
                ],
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'OpenAI request failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('OpenAI returned an empty response.');
        }

        $parsed = json_decode($content, true);

        if (! is_array($parsed)) {
            throw new RuntimeException('OpenAI returned invalid JSON.');
        }

        return [
            'suggestions' => $parsed['suggestions'] ?? [],
            'notes'       => $parsed['notes'] ?? [],
            'provider'    => 'openai',
            'model'       => (string) config('ai.openai.model', 'gpt-4o-mini'),
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
