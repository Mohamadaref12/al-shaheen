<?php

namespace App\Services\News;

use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsWorkspaceService
{
    public const WORKSPACE_DRAFT_STATUSES = ['draft', 'under_review'];

    public const STATUS_LABELS = [
        'draft'        => 'Draft',
        'under_review' => 'In Review',
        'published'    => 'Published',
        'archived'     => 'Archived',
    ];

    public function draftsResponse(Request $request, User $user, bool $allowAllForEditors = true): JsonResponse
    {
        $query = News::query()
            ->whereIn('status', self::WORKSPACE_DRAFT_STATUSES)
            ->with(['category:id,name,slug', 'translations'])
            ->orderByDesc('updated_at');

        if (! $allowAllForEditors || ! $this->userIsNewsEditor($user) || ! $request->boolean('all')) {
            $query->where('author_id', $user->id);
        }

        $drafts = $query->get();
        $rows = $drafts->map(fn (News $news) => $this->formatDraftRow($news));
        $readinessValues = $rows->pluck('readiness')->filter();

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => 'News drafts retrieved successfully.',
            'data'    => [
                'summary' => [
                    'total_drafts'     => $drafts->count(),
                    'ready_to_publish' => $drafts->where('status', 'under_review')->count(),
                    'avg_completion'   => $readinessValues->isNotEmpty()
                        ? (int) round($readinessValues->avg())
                        : 0,
                ],
                'drafts' => $rows->values(),
            ],
        ]);
    }

    public function paginatedList(Request $request, User $user): array
    {
        $authorId = $user->id;

        $query = News::query()
            ->where('author_id', $authorId)
            ->with(['category:id,name,slug', 'translations']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->whereHas('translations', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")));
        }

        match ($request->input('sort', 'latest')) {
            'views'  => $query->orderByDesc('views_count'),
            'oldest' => $query->orderBy('updated_at'),
            default  => $query->orderByDesc('updated_at'),
        };

        $paginator = $query->paginate($request->input('per_page', 15));

        return [
            'items'   => collect($paginator->items())->map(fn (News $news) => $this->formatNewsRow($news))->values(),
            'summary' => $this->buildNewsSummary($authorId),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
    }

    public function formatNewsRow(News $news): array
    {
        return [
            'id'                 => $news->id,
            'title'              => $news->display_title,
            'slug'               => $news->slug,
            'status'             => $news->status,
            'status_label'       => self::STATUS_LABELS[$news->status] ?? $news->status,
            'category'           => $news->category,
            'featured_image_url' => $news->featured_image_url,
            'is_breaking'        => (bool) $news->is_breaking,
            'is_premium'         => (bool) $news->is_premium,
            'read_time'          => $news->read_time,
            'published_at'       => $news->published_at?->toIso8601String(),
            'updated_at'         => $news->updated_at?->toIso8601String(),
            'published_label'    => $this->formatPublishedLabel($news),
            'views_count'        => $news->views_count,
            'views_formatted'    => $this->formatCompactNumber((int) $news->views_count),
        ];
    }

    public function buildNewsSummary(int $authorId): array
    {
        $news = News::query()->where('author_id', $authorId);

        $totalNews = (int) (clone $news)->count();
        $published = (clone $news)->where('status', 'published');
        $totalViews = (int) (clone $published)->sum('views_count');

        return [
            'total_news'            => $totalNews,
            'published_count'       => (int) (clone $published)->count(),
            'draft_count'           => (int) (clone $news)->whereIn('status', self::WORKSPACE_DRAFT_STATUSES)->count(),
            'total_views'           => $totalViews,
            'total_views_formatted' => $this->formatCompactNumber($totalViews),
        ];
    }

    public function previewResponse(User $user, int $newsId): JsonResponse
    {
        $news = News::query()
            ->with(['author:id,name', 'category:id,name,slug', 'translations'])
            ->where('id', $newsId)
            ->whereNot('status', 'archived')
            ->first();

        if (! $news || ! $this->userCanViewNewsWorkspace($user, $news)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'News not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => 'News preview retrieved successfully.',
            'data'    => [
                'id'                 => $news->id,
                'author_id'          => $news->author_id,
                'category_id'        => $news->category_id,
                'featured_image'     => $news->featured_image,
                'featured_image_url' => $news->featured_image_url,
                'video_embed'        => $news->video_embed,
                'read_time'          => $news->read_time,
                'is_breaking'        => (bool) $news->is_breaking,
                'is_premium'         => (bool) $news->is_premium,
                'status'             => $news->status,
                'status_label'       => self::STATUS_LABELS[$news->status] ?? $news->status,
                'published_at'       => $news->published_at?->toIso8601String(),
                'published_label'    => $this->formatPublishedLabel($news),
                'is_preview'         => $news->status !== 'published',
                'created_at'         => $news->created_at?->toIso8601String(),
                'updated_at'         => $news->updated_at?->toIso8601String(),
                'author'             => $news->author,
                'category'           => $news->category,
                'translations'       => $news->translations->mapWithKeys(fn ($translation) => [
                    $translation->locale => [
                        'title'           => $translation->title,
                        'subtitle'        => $translation->subtitle,
                        'slug'            => $translation->slug,
                        'excerpt'         => $translation->excerpt,
                        'content'         => $translation->content,
                        'seo_title'       => $translation->seo_title,
                        'seo_description' => $translation->seo_description,
                    ],
                ]),
            ],
        ]);
    }

    public function formatDraftRow(News $news): array
    {
        return [
            'id'                => $news->id,
            'title'             => $news->display_title,
            'slug'              => $news->slug,
            'status'            => $news->status,
            'status_label'      => self::STATUS_LABELS[$news->status] ?? $news->status,
            'category'          => $news->category,
            'last_edited_at'    => $news->updated_at?->toIso8601String(),
            'last_edited_label' => $this->formatLastEditedLabel($news->updated_at),
            'readiness'         => $this->calculateReadiness($news),
            'word_count'        => $this->newsWordCount($news),
        ];
    }

    public function userCanViewNewsWorkspace(User $user, News $news): bool
    {
        if ($this->userIsNewsEditor($user)) {
            return true;
        }

        return $news->author_id === $user->id
            && ($user->writer()->exists() || $user->contributor()->exists());
    }

    private function userIsNewsEditor(User $user): bool
    {
        return $user->editor()->exists() || $user->admin()->exists();
    }

    private function calculateReadiness(News $news): int
    {
        if ($news->status === 'under_review') {
            return min(100, max(85, $this->calculateContentScore($news) + 10));
        }

        $score = 0;

        if ($this->hasFilledTitle($news)) {
            $score += 15;
        }

        if ($this->hasFilledExcerpt($news)) {
            $score += 15;
        }

        if ($news->category_id) {
            $score += 10;
        }

        if (filled($news->featured_image)) {
            $score += 10;
        }

        $score += $this->calculateContentScore($news);

        return min(100, max(0, $score));
    }

    private function calculateContentScore(News $news): int
    {
        $wordCount = $this->newsWordCount($news);

        return min(50, (int) round(($wordCount / 1500) * 50));
    }

    private function newsWordCount(News $news): int
    {
        if ($news->relationLoaded('translations')) {
            return (int) $news->translations
                ->map(fn ($translation) => $this->countWords($translation->content ?? ''))
                ->max();
        }

        return $this->countWords($news->content ?? '');
    }

    private function hasFilledTitle(News $news): bool
    {
        if ($news->relationLoaded('translations')) {
            return $news->translations->contains(fn ($translation) => filled($translation->title));
        }

        return filled($news->title);
    }

    private function hasFilledExcerpt(News $news): bool
    {
        if ($news->relationLoaded('translations')) {
            return $news->translations->contains(fn ($translation) => filled($translation->excerpt));
        }

        return filled($news->excerpt);
    }

    private function countWords(string $content): int
    {
        $text = trim(strip_tags($content));

        if ($text === '') {
            return 0;
        }

        return str_word_count($text);
    }

    private function formatLastEditedLabel(?Carbon $updatedAt): string
    {
        if (! $updatedAt) {
            return 'Not edited yet';
        }

        return 'Edited ' . $updatedAt->diffForHumans();
    }

    private function formatPublishedLabel(News $news): string
    {
        if ($news->status !== 'published' || ! $news->published_at) {
            return 'Not published yet';
        }

        return 'Published ' . $news->published_at->diffForHumans();
    }

    private function formatCompactNumber(int $number): string
    {
        if ($number >= 1_000_000) {
            return round($number / 1_000_000, 1) . 'M';
        }

        if ($number >= 1_000) {
            return round($number / 1_000, 1) . 'K';
        }

        return (string) $number;
    }
}
