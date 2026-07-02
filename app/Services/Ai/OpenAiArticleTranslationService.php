<?php

namespace App\Services\Ai;

use App\Contracts\ArticleTranslationService;
use App\Services\Ai\Concerns\InteractsWithOpenAi;
use App\Support\AiSettings;

class OpenAiArticleTranslationService implements ArticleTranslationService
{
    use InteractsWithOpenAi;

    public function isAvailable(): bool
    {
        return $this->isConfigured();
    }

    public function translate(array $snapshot): array
    {
        $sourceLocale = (string) ($snapshot['source_locale'] ?? 'ar');
        $targetLocale = (string) ($snapshot['target_locale'] ?? ($sourceLocale === 'ar' ? 'en' : 'ar'));

        $parsed = $this->chatJson([
            [
                'role'    => 'system',
                'content' => $this->systemPrompt($sourceLocale, $targetLocale),
            ],
            [
                'role'    => 'user',
                'content' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            ],
        ]);

        $suggestions = $this->mapSuggestionsToTargetLocale(
            $parsed['suggestions'] ?? [],
            $targetLocale
        );

        return [
            'suggestions' => $suggestions,
            'notes'       => $parsed['notes'] ?? [],
            'provider'    => 'openai',
            'model'       => AiSettings::model(),
        ];
    }

    protected function systemPrompt(string $sourceLocale, string $targetLocale): string
    {
        $sourceLabel = $sourceLocale === 'ar' ? 'Arabic' : 'English';
        $targetLabel = $targetLocale === 'ar' ? 'Arabic' : 'English';

        return <<<PROMPT
You are a professional news translator for a bilingual publication (Arabic / English).

Translate the article fields from {$sourceLabel} to {$targetLabel}.

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
- Translate ONLY fields that were provided and non-empty in the input snapshot.
- Do NOT invent facts. Preserve meaning, tone, and journalistic style.
- Preserve HTML structure in content if present.
- seo fields should remain concise and natural in {$targetLabel}.
- suggestions values must be in {$targetLabel} only.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $suggestions
     * @return array<string, mixed>
     */
    protected function mapSuggestionsToTargetLocale(array $suggestions, string $targetLocale): array
    {
        $mapped = [];

        foreach ($suggestions as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (preg_match('/_(ar|en)$/', (string) $field)) {
                $mapped[$field] = $value;

                continue;
            }

            $mapped["{$field}_{$targetLocale}"] = $value;
        }

        return $mapped;
    }
}
