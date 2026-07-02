<?php

namespace App\Actions;

use App\Contracts\NewsImprovementService;
use App\Models\News;
use App\Models\NewsAiSuggestion;
use App\Models\User;
use App\Support\AiSettings;
use App\Traits\BuildsNewsAiSnapshot;
use Throwable;

class GenerateNewsSuggestionsAction
{
    use BuildsNewsAiSnapshot;

    public function __construct(
        protected NewsImprovementService $improvementService
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function execute(User $user, array $input, ?News $news = null): array
    {
        if (! $this->improvementService->isAvailable()) {
            return [
                'available'  => false,
                'message'    => 'AI news improvement is not enabled. Add your OpenAI API key in Admin → AI Settings.',
                'suggestion' => null,
            ];
        }

        $snapshot = $this->buildImprovementSnapshot($input, $news);
        $focus    = (string) ($input['focus'] ?? 'all');
        $locale   = (string) ($snapshot['locale'] ?? 'ar');

        if (! filled($snapshot['title'] ?? null) && ! filled($snapshot['content'] ?? null) && ! filled($snapshot['excerpt'] ?? null)) {
            return [
                'available'  => false,
                'message'    => 'No content found to improve. Provide title/content in Arabic or English.',
                'suggestion' => null,
            ];
        }

        try {
            $result = $this->improvementService->improve($snapshot, $focus);

            $record = NewsAiSuggestion::create([
                'news_id'           => $news?->id,
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
                'message'    => 'AI suggestions generated successfully. Review and apply via news create or update.',
                'suggestion' => $record,
                'apply_hint' => $news
                    ? 'Copy fields from suggestions into PUT /news/{id}. Nothing is auto-applied.'
                    : 'Copy fields from suggestions into the create form, then POST /news. Nothing is auto-applied.',
            ];
        } catch (Throwable $e) {
            report($e);

            NewsAiSuggestion::create([
                'news_id'           => $news?->id,
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

            return [
                'available'  => true,
                'message'    => 'Failed to generate AI suggestions.',
                'suggestion' => null,
                'error'      => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }
}
