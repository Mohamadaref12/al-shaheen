<?php

namespace App\Actions;

use App\Contracts\ArticleImprovementService;
use App\Models\Article;
use App\Models\ArticleAiSuggestion;
use App\Models\User;
use Throwable;

class GenerateArticleSuggestionsAction
{
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
                'message'    => 'AI article improvement is not enabled yet.',
                'suggestion' => null,
            ];
        }

        $snapshot = $this->buildSnapshot($input, $article);
        $focus    = (string) ($input['focus'] ?? 'all');
        $locale   = (string) ($input['locale'] ?? $snapshot['locale'] ?? 'ar');

        try {
            $result = $this->improvementService->improve($snapshot, $focus);

            $record = ArticleAiSuggestion::create([
                'article_id'        => $article?->id,
                'user_id'           => $user->id,
                'focus'             => $focus,
                'locale'            => $locale,
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
                    'focus'             => $focus,
                    'locale'            => $locale,
                    'original_snapshot' => $snapshot,
                    'suggestions'       => [],
                    'notes'             => [],
                    'provider'          => config('ai.provider'),
                    'model'             => config('ai.openai.model'),
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

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function buildSnapshot(array $input, ?Article $article = null): array
    {
        $fields = ['title', 'subtitle', 'content', 'excerpt', 'seo_title', 'seo_description', 'locale'];

        $snapshot = [
            'locale' => $input['locale'] ?? $article?->locale ?? 'ar',
        ];

        foreach ($fields as $field) {
            if ($field === 'locale') {
                continue;
            }

            $snapshot[$field] = array_key_exists($field, $input)
                ? $input[$field]
                : $article?->{$field};
        }

        return array_filter(
            $snapshot,
            fn ($value) => $value !== null && $value !== ''
        );
    }
}
