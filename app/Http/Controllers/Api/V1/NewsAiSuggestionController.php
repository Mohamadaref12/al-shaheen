<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GenerateNewsSuggestionsAction;
use App\Actions\GenerateNewsTranslationAction;
use App\Contracts\NewsImprovementService;
use App\Contracts\NewsTranslationService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsAiSuggestionResource;
use App\Models\News;
use App\Models\NewsAiSuggestion;
use App\Traits\NormalizesTranslatableApiInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NewsAiSuggestionController extends Controller
{
    use NormalizesTranslatableApiInput;

    public function status(
        NewsImprovementService $improvementService,
        NewsTranslationService $translationService
    ): JsonResponse {
        return $this->success([
            'available'             => $improvementService->isAvailable(),
            'translation_available' => $translationService->isAvailable(),
            'provider'              => config('ai.provider'),
            'enabled'               => $improvementService->isAvailable() || $translationService->isAvailable(),
        ], 'AI status retrieved successfully.');
    }

    public function suggestFromDraft(Request $request, GenerateNewsSuggestionsAction $action): JsonResponse
    {
        try {
            return $this->generateImprovement($request, $action);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate AI suggestions.');
        }
    }

    public function suggestForNews(
        Request $request,
        int $newsId,
        GenerateNewsSuggestionsAction $action
    ): JsonResponse {
        try {
            $news = News::query()->with('translations')->find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            if (! $this->canManageNews($request, $news)) {
                return $this->error(null, 'You are not authorized to improve this news item.', 403);
            }

            return $this->generateImprovement($request, $action, $news);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate AI suggestions.');
        }
    }

    public function translateFromDraft(Request $request, GenerateNewsTranslationAction $action): JsonResponse
    {
        try {
            return $this->generateTranslation($request, $action);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate translation.');
        }
    }

    public function translateForNews(
        Request $request,
        int $newsId,
        GenerateNewsTranslationAction $action
    ): JsonResponse {
        try {
            $news = News::query()->with('translations')->find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            if (! $this->canManageNews($request, $news)) {
                return $this->error(null, 'You are not authorized to translate this news item.', 403);
            }

            return $this->generateTranslation($request, $action, $news);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to generate translation.');
        }
    }

    public function index(Request $request, int $newsId): JsonResponse
    {
        try {
            $news = News::find($newsId);

            if (! $news) {
                return $this->error(null, 'News not found.', 404);
            }

            if (! $this->canManageNews($request, $news)) {
                return $this->error(null, 'You are not authorized to view AI suggestions for this news item.', 403);
            }

            $request->validate([
                'per_page' => 'nullable|integer|min:1|max:20',
                'kind'     => 'nullable|in:improvement,translation',
            ]);

            $paginator = NewsAiSuggestion::query()
                ->where('news_id', $newsId)
                ->where('status', 'completed')
                ->when($request->filled('kind'), fn ($query) => $query->where('kind', $request->input('kind')))
                ->orderByDesc('id')
                ->paginate((int) $request->input('per_page', 10));

            return $this->pagedSuccess(
                NewsAiSuggestionResource::collection($paginator->items())->resolve(),
                [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'News AI suggestions retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve AI suggestions.');
        }
    }

    protected function generateImprovement(
        Request $request,
        GenerateNewsSuggestionsAction $action,
        ?News $news = null
    ): JsonResponse {
        $user = $request->user();

        if (! $this->userCanUseNewsAi($user)) {
            return $this->error(null, 'You are not authorized to use AI suggestions.', 403);
        }

        $this->prepareTranslatableRequest($request);

        $data = $request->validate($this->improvementValidationRules($news !== null));
        $this->mapLegacyTranslationInput($data);

        $result = $action->execute($user, $data, $news);

        return $this->aiResponse($result);
    }

    protected function generateTranslation(
        Request $request,
        GenerateNewsTranslationAction $action,
        ?News $news = null
    ): JsonResponse {
        $user = $request->user();

        if (! $this->userCanUseNewsAi($user)) {
            return $this->error(null, 'You are not authorized to use AI translation.', 403);
        }

        $this->prepareTranslatableRequest($request);

        $data = $request->validate($this->translationValidationRules($news !== null));
        $this->mapLegacyTranslationInput($data);

        $result = $action->execute($user, $data, $news);

        return $this->aiResponse($result);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function aiResponse(array $result): JsonResponse
    {
        $payload = [
            'available'  => $result['available'],
            'message'    => $result['message'],
            'suggestion' => $result['suggestion']
                ? NewsAiSuggestionResource::make($result['suggestion'])->resolve()
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

    protected function improvementValidationRules(bool $newsExists): array
    {
        return array_merge($this->sharedContentValidationRules($newsExists), [
            'focus'  => 'nullable|in:all,grammar,seo,clarity,headline',
            'locale' => 'nullable|in:ar,en',
        ]);
    }

    protected function translationValidationRules(bool $newsExists): array
    {
        return array_merge($this->sharedContentValidationRules($newsExists), [
            'locale'        => 'nullable|in:ar,en',
            'source_locale' => 'nullable|in:ar,en',
            'target_locale' => 'nullable|in:ar,en',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedContentValidationRules(bool $newsExists): array
    {
        $contentRules = $newsExists
            ? $this->translatableRichTextRules()
            : array_merge(['required_without_all:title_ar,title_en,content_ar,content_en,title,content'], $this->translatableRichTextRules());

        return [
            'title_en'           => 'nullable|string|max:500',
            'title_ar'           => 'nullable|string|max:500',
            'subtitle_en'        => 'nullable|string|max:500',
            'subtitle_ar'        => 'nullable|string|max:500',
            'content_en'         => $this->translatableRichTextRules(),
            'content_ar'         => $this->translatableRichTextRules(),
            'excerpt_en'         => $this->translatableRichTextRules(),
            'excerpt_ar'         => $this->translatableRichTextRules(),
            'title'              => 'nullable|string|max:500',
            'subtitle'           => 'nullable|string|max:500',
            'content'            => $contentRules,
            'excerpt'            => $this->translatableRichTextRules(),
            'seo_title_en'       => 'nullable|string|max:200',
            'seo_title_ar'       => 'nullable|string|max:200',
            'seo_description_en' => 'nullable|string|max:400',
            'seo_description_ar' => 'nullable|string|max:400',
            'seo_title'          => 'nullable|string|max:200',
            'seo_description'    => 'nullable|string|max:400',
        ];
    }

    protected function canManageNews(Request $request, News $news): bool
    {
        $user = $request->user();

        if ($user->editor()->exists() || $user->admin()->exists()) {
            return true;
        }

        return $news->author_id === $user->id
            && ($user->writer()->exists() || $user->contributor()->exists());
    }

    protected function userCanUseNewsAi($user): bool
    {
        return $user->writer()->exists()
            || $user->contributor()->exists()
            || $user->editor()->exists()
            || $user->admin()->exists();
    }
}
