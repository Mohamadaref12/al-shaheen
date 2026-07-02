<?php

namespace App\Actions;

use App\Contracts\ArticleTranslationService;
use App\Models\Article;
use App\Models\ArticleAiSuggestion;
use App\Models\User;
use App\Traits\BuildsArticleAiSnapshot;
use App\Support\AiSettings;
use Throwable;

class GenerateArticleTranslationAction
{
    use BuildsArticleAiSnapshot;

    public function __construct(
        protected ArticleTranslationService $translationService
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function execute(User $user, array $input, ?Article $article = null): array
    {
        if (! $this->translationService->isAvailable()) {
            return [
                'available'  => false,
                'message'    => 'AI article translation is not enabled. Add your OpenAI API key in Admin → AI Settings.',
                'suggestion' => null,
            ];
        }

        $snapshot = $this->buildTranslationSnapshot($input, $article);

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

            $record = ArticleAiSuggestion::create([
                'article_id'        => $article?->id,
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
                'message'        => 'Translation generated successfully. Review suggestions and apply via article update.',
                'suggestion'     => $record,
                'source_locale'  => $sourceLocale,
                'target_locale'  => $targetLocale,
                'apply_hint'     => 'Copy fields from suggestions (e.g. title_en, content_en) into PUT /articles/{id}. Nothing is auto-applied.',
            ];
        } catch (Throwable $e) {
            report($e);

            ArticleAiSuggestion::create([
                'article_id'        => $article?->id,
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
