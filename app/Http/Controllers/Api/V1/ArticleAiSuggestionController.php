<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GenerateArticleSuggestionsAction;
use App\Actions\GenerateArticleTranslationAction;
use App\Contracts\ArticleImprovementService;
use App\Contracts\ArticleTranslationService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleAiSuggestionResource;
use App\Models\Article;
use App\Models\ArticleAiSuggestion;
use App\Traits\NormalizesTranslatableApiInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ArticleAiSuggestionController extends Controller
{
    use NormalizesTranslatableApiInput;

    public function status(
        ArticleImprovementService $improvementService,
        ArticleTranslationService $translationService
    ): JsonResponse {
        return $this->success([
            'available'              => $improvementService->isAvailable(),
            'translation_available'  => $translationService->isAvailable(),
            'provider'               => config('ai.provider'),
            'enabled'                => $improvementService->isAvailable() || $translationService->isAvailable(),
        ], 'AI status retrieved successfully.');
    }

    public function suggestFromDraft(Request $request, GenerateArticleSuggestionsAction $action): JsonResponse
    {
        try {
            return $this->generateImprovement($request, $action);
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
            $article = Article::query()->with('translations')->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            if (! $this->canManageArticle($request, $article)) {
                return $this->error(null, 'You are not authorized to improve this article.', 403);
            }

            return $this->generateImprovement($request, $action, $article);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate AI suggestions.');
        }
    }

    public function translateFromDraft(Request $request, GenerateArticleTranslationAction $action): JsonResponse
    {
        try {
            return $this->generateTranslation($request, $action);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate translation.');
        }
    }

    public function translateForArticle(
        Request $request,
        int $articleId,
        GenerateArticleTranslationAction $action
    ): JsonResponse {
        try {
            $article = Article::query()->with('translations')->find($articleId);

            if (! $article) {
                return $this->error(null, 'Article not found.', 404);
            }

            if (! $this->canManageArticle($request, $article)) {
                return $this->error(null, 'You are not authorized to translate this article.', 403);
            }

            return $this->generateTranslation($request, $action, $article);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate translation.');
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
                'kind'     => 'nullable|in:improvement,translation',
            ]);

            $paginator = ArticleAiSuggestion::query()
                ->where('article_id', $articleId)
                ->where('status', 'completed')
                ->when($request->filled('kind'), fn ($query) => $query->where('kind', $request->input('kind')))
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

    protected function generateImprovement(
        Request $request,
        GenerateArticleSuggestionsAction $action,
        ?Article $article = null
    ): JsonResponse {
        $user = $request->user();

        if (! $user->writer()->exists() && ! $user->editor()->exists() && ! $user->admin()->exists()) {
            return $this->error(null, 'You are not authorized to use AI suggestions.', 403);
        }

        $this->prepareTranslatableRequest($request);

        $data = $request->validate($this->improvementValidationRules($article !== null));
        $this->mapLegacyTranslationInput($data);

        $result = $action->execute($request->user(), $data, $article);

        return $this->aiResponse($result, 'AI suggestions generated successfully.');
    }

    protected function generateTranslation(
        Request $request,
        GenerateArticleTranslationAction $action,
        ?Article $article = null
    ): JsonResponse {
        $user = $request->user();

        if (! $user->writer()->exists() && ! $user->editor()->exists() && ! $user->admin()->exists()) {
            return $this->error(null, 'You are not authorized to use AI translation.', 403);
        }

        $this->prepareTranslatableRequest($request);

        $data = $request->validate($this->translationValidationRules($article !== null));
        $this->mapLegacyTranslationInput($data);

        $result = $action->execute($request->user(), $data, $article);

        return $this->aiResponse($result, 'Translation generated successfully.');
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function aiResponse(array $result, string $successMessage): JsonResponse
    {
        $payload = [
            'available'  => $result['available'],
            'message'    => $result['message'],
            'suggestion' => $result['suggestion']
                ? ArticleAiSuggestionResource::make($result['suggestion'])->resolve()
                : null,
        ];

        foreach (['source_locale', 'target_locale', 'apply_hint', 'error'] as $key) {
            if (array_key_exists($key, $result)) {
                $payload[$key] = $result[$key];
            }
        }

        return $this->success(
            $payload,
            $result['message'],
            $result['available'] && $result['suggestion'] ? 201 : 200
        );
    }

    protected function improvementValidationRules(bool $articleExists): array
    {
        return array_merge($this->sharedContentValidationRules($articleExists), [
            'focus'  => 'nullable|in:all,grammar,seo,clarity,headline',
            'locale' => 'nullable|in:ar,en',
        ]);
    }

    protected function translationValidationRules(bool $articleExists): array
    {
        return array_merge($this->sharedContentValidationRules($articleExists), [
            'locale'        => 'nullable|in:ar,en',
            'source_locale' => 'nullable|in:ar,en',
            'target_locale' => 'nullable|in:ar,en',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedContentValidationRules(bool $articleExists): array
    {
        $contentRules = $articleExists
            ? $this->translatableRichTextRules()
            : array_merge(['required_without_all:title_ar,title_en,content_ar,content_en,title,content'], $this->translatableRichTextRules());

        return [
            'title_en'            => 'nullable|string|max:500',
            'title_ar'            => 'nullable|string|max:500',
            'subtitle_en'         => 'nullable|string|max:500',
            'subtitle_ar'         => 'nullable|string|max:500',
            'content_en'          => $this->translatableRichTextRules(),
            'content_ar'          => $this->translatableRichTextRules(),
            'excerpt_en'          => $this->translatableRichTextRules(),
            'excerpt_ar'          => $this->translatableRichTextRules(),
            'title'               => 'nullable|string|max:500',
            'subtitle'            => 'nullable|string|max:500',
            'content'             => $contentRules,
            'excerpt'             => $this->translatableRichTextRules(),
            'seo_title_en'        => 'nullable|string|max:200',
            'seo_title_ar'        => 'nullable|string|max:200',
            'seo_description_en'  => 'nullable|string|max:400',
            'seo_description_ar'  => 'nullable|string|max:400',
            'seo_title'           => 'nullable|string|max:200',
            'seo_description'     => 'nullable|string|max:400',
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
