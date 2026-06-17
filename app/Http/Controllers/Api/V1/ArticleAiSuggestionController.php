<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GenerateArticleSuggestionsAction;
use App\Contracts\ArticleImprovementService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleAiSuggestionResource;
use App\Models\Article;
use App\Models\ArticleAiSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ArticleAiSuggestionController extends Controller
{
    public function status(ArticleImprovementService $improvementService): JsonResponse
    {
        return $this->success([
            'available' => $improvementService->isAvailable(),
            'provider'  => config('ai.provider'),
            'enabled'   => (bool) config('ai.enabled'),
        ], 'AI status retrieved successfully.');
    }

    public function suggestFromDraft(Request $request, GenerateArticleSuggestionsAction $action): JsonResponse
    {
        try {
            return $this->generate($request, $action);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate AI suggestions.');
        }
    }

    public function suggestForArticle(
        Request $request,
        int $articleId,
        GenerateArticleSuggestionsAction $action
    ): JsonResponse {
        try {
            $article = Article::find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            if (! $this->canManageArticle($request, $article)) {
                return $this->error(null, 'You are not authorized to improve this article.', 403);
            }

            return $this->generate($request, $action, $article);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate AI suggestions.');
        }
    }

    public function index(Request $request, int $articleId): JsonResponse
    {
        try {
            $article = Article::find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            if (! $this->canManageArticle($request, $article)) {
                return $this->error(null, 'You are not authorized to view AI suggestions for this article.', 403);
            }

            $request->validate([
                'per_page' => 'nullable|integer|min:1|max:20',
            ]);

            $paginator = ArticleAiSuggestion::query()
                ->where('article_id', $articleId)
                ->where('status', 'completed')
                ->orderByDesc('id')
                ->paginate((int) $request->input('per_page', 10));

            return $this->pagedSuccess(
                ArticleAiSuggestionResource::collection($paginator->items())->resolve(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'Article AI suggestions retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve AI suggestions.');
        }
    }

    protected function generate(
        Request $request,
        GenerateArticleSuggestionsAction $action,
        ?Article $article = null
    ): JsonResponse {
        $user = $request->user();

        if (! $user->writer()->exists() && ! $user->editor()->exists() && ! $user->admin()->exists()) {
            return $this->error(null, 'You are not authorized to use AI suggestions.', 403);
        }

        $data = $request->validate($this->suggestionValidationRules($article !== null));

        $result = $action->execute($user, $data, $article);

        $payload = [
            'available'  => $result['available'],
            'message'    => $result['message'],
            'suggestion' => $result['suggestion']
                ? ArticleAiSuggestionResource::make($result['suggestion'])->resolve()
                : null,
        ];

        if (isset($result['error'])) {
            $payload['error'] = $result['error'];
        }

        return $this->success(
            $payload,
            $result['message'],
            $result['available'] && $result['suggestion'] ? 201 : 200
        );
    }

    protected function suggestionValidationRules(bool $articleExists): array
    {
        $contentRules = $articleExists
            ? ['sometimes', 'nullable', 'string']
            : ['required_without_all:title,content', 'nullable', 'string'];

        return [
            'focus'             => 'nullable|in:all,grammar,seo,clarity,headline',
            'locale'            => 'nullable|in:ar,en',
            'title'             => 'nullable|string|max:500',
            'subtitle'          => 'nullable|string|max:500',
            'content'           => $contentRules,
            'excerpt'           => 'nullable|string|max:1000',
            'seo_title'         => 'nullable|string|max:200',
            'seo_description'   => 'nullable|string|max:400',
        ];
    }

    protected function canManageArticle(Request $request, Article $article): bool
    {
        $user = $request->user();

        return $article->author_id === $user->id
            || $user->editor()->exists()
            || $user->admin()->exists();
    }
}
