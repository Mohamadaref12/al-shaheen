<?php

namespace App\Actions;

use App\Contracts\NewsTranslationService;
use App\Models\News;
use App\Models\NewsAiSuggestion;
use App\Models\User;
use App\Support\AiSettings;
use App\Traits\BuildsNewsAiSnapshot;
use Throwable;

class GenerateNewsTranslationAction
{
    use BuildsNewsAiSnapshot;

    public function __construct(
        protected NewsTranslationService $translationService
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function execute(User $user, array $input, ?News $news = null): array
    {
        if (! $this->translationService->isAvailable()) {
            return [
                'available'  => false,
                'message'    => 'AI news translation is not enabled. Add your OpenAI API key in Admin → AI Settings.',
                'suggestion' => null,
            ];
        }

        $snapshot = $this->buildTranslationSnapshot($input, $news);

        if (! filled($snapshot['title'] ?? null) && ! filled($snapshot['content'] ?? null) && ! filled($snapshot['excerpt'] ?? null)) {
            return [
                'available'  => false,
                'message'    => 'No source content found to translate. Provide title/content in Arabic or English.',
                'suggestion' => null,
            ];
        }

        $sourceLocale = (string) $snapshot['source_locale'];
        $targetLocale = (string) $snapshot['target_locale'];

        try {
            $result = $this->translationService->translate($snapshot);

            $record = NewsAiSuggestion::create([
                'news_id'           => $news?->id,
                'user_id'           => $user->id,
                'kind'              => 'translation',
                'focus'             => 'translate',
                'locale'            => $targetLocale,
                'source_locale'     => $sourceLocale,
                'target_locale'     => $targetLocale,
                'original_snapshot' => $snapshot,
                'suggestions'       => $result['suggestions'],
                'notes'             => $result['notes'],
                'provider'          => $result['provider'],
                'model'             => $result['model'],
                'status'            => 'completed',
            ]);

            return [
                'available'      => true,
                'message'        => 'Translation generated successfully. Review suggestions and apply via news update.',
                'suggestion'     => $record,
                'source_locale'  => $sourceLocale,
                'target_locale'  => $targetLocale,
                'apply_hint'     => $news
                    ? 'Copy fields from suggestions (e.g. title_en, content_en) into PUT /news/{id}. Nothing is auto-applied.'
                    : 'Copy fields from suggestions into the create form, then POST /news with both locales. Nothing is auto-applied.',
            ];
        } catch (Throwable $e) {
            report($e);

            NewsAiSuggestion::create([
                'news_id'           => $news?->id,
                'user_id'           => $user->id,
                'kind'              => 'translation',
                'focus'             => 'translate',
                'locale'            => $targetLocale,
                'source_locale'     => $sourceLocale,
                'target_locale'     => $targetLocale,
                'original_snapshot' => $snapshot,
                'suggestions'       => [],
                'notes'             => [],
                'provider'          => config('ai.provider'),
                'model'             => AiSettings::model(),
                'status'            => 'failed',
            ]);

            return [
                'available'  => true,
                'message'    => 'Failed to generate translation.',
                'suggestion' => null,
                'error'      => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }
}
