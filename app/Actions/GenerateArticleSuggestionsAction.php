<?php

namespace App\Actions;

use App\Contracts\ArticleImprovementService;
use App\Models\Article;
use App\Models\ArticleAiSuggestion;
use App\Models\User;
use App\Support\AiSettings;
use App\Traits\BuildsArticleAiSnapshot;
use Throwable;

class GenerateArticleSuggestionsAction
{
    use BuildsArticleAiSnapshot;

    public function __construct(
        protected ArticleImprovementService $improvementService
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function execute(User $user, array $input, ?Article $article = null): array
    {
        if (! $this->improvementService->isAvailable()) {
            return [
                'available'  => false,
                'message'    => 'AI article improvement is not enabled. Add your OpenAI API key in Admin → AI Settings.',
                'suggestion' => null,
            ];
        }

        $snapshot = $this->buildImprovementSnapshot($input, $article);
        $focus    = (string) ($input['focus'] ?? 'all');
        $locale   = (string) ($snapshot['locale'] ?? 'ar');

        try {
            $result = $this->improvementService->improve($snapshot, $focus);

            $record = ArticleAiSuggestion::create([
                'article_id'        => $article?->id,
                'user_id'           => $user->id,
                'kind'              => 'improvement',
                'focus'             => $focus,
                'locale'            => $locale,
                'source_locale'     => $locale,
                'target_locale'     => $locale,
                'original_snapshot' => $snapshot,
                'suggestions'       => $result['suggestions'],
                'notes'             => $result['notes'],
                'provider'          => $result['provider'],
                'model'             => $result['model'],
                'status'            => 'completed',
            ]);

            return [
                'available'  => true,
                'message'    => 'AI suggestions generated successfully.',
                'suggestion' => $record,
            ];
        } catch (Throwable $e) {
            report($e);

            if ($article || filled($snapshot['title'] ?? null) || filled($snapshot['content'] ?? null)) {
                ArticleAiSuggestion::create([
                    'article_id'        => $article?->id,
                    'user_id'           => $user->id,
                    'kind'              => 'improvement',
                    'focus'             => $focus,
                    'locale'            => $locale,
                    'source_locale'     => $locale,
                    'target_locale'     => $locale,
                    'original_snapshot' => $snapshot,
                    'suggestions'       => [],
                    'notes'             => [],
                    'provider'          => config('ai.provider'),
                    'model'             => AiSettings::model(),
                    'status'            => 'failed',
                ]);
            }

            return [
                'available'  => true,
                'message'    => 'Failed to generate AI suggestions.',
                'suggestion' => null,
                'error'      => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }
}
