<?php

namespace App\Services\Ai\Concerns;

use App\Support\AiSettings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

trait InteractsWithOpenAi
{
    public function isConfigured(): bool
    {
        return AiSettings::isConfigured();
    }

    /**
     * @param  array<int, array<string, string>>  $messages
     * @return array<string, mixed>
     */
    protected function chatJson(array $messages, float $temperature = 0.3): array
    {
        $response = Http::baseUrl(rtrim(AiSettings::baseUrl(), '/'))
            ->withToken((string) AiSettings::apiKey())
            ->timeout(AiSettings::timeout())
            ->post('/chat/completions', [
                'model'           => AiSettings::model(),
                'response_format' => ['type' => 'json_object'],
                'messages'        => $messages,
                'temperature'     => $temperature,
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

        return $parsed;
    }
}
