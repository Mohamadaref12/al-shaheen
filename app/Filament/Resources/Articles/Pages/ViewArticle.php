<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\DownloadArticlePdfAction;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Article;
use App\Models\Comment;
use App\Support\ContentEditability;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;

    #[Url(as: 'lang')]
    public string $previewLocale = 'en';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (! in_array($this->previewLocale, ['ar', 'en'], true)) {
            $this->previewLocale = 'en';
        }
    }

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    public function getTitle(): string | Htmlable
    {
        return 'Article Preview';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('togglePreviewLocale')
                ->label(fn (): string => $this->previewLocale === 'ar' ? 'English' : 'العربية')
                ->icon(Heroicon::OutlinedLanguage)
                ->color('gray')
                ->action(function (): void {
                    $this->previewLocale = $this->previewLocale === 'ar' ? 'en' : 'ar';
                }),
            ...ContentStatusActions::forArticle(
                fn (): Article => $this->getRecord(),
                fn () => $this->record->refresh(),
            ),
            DownloadArticlePdfAction::make(),
            EditAction::make()
                ->visible(fn (): bool => ContentEditability::userCanEditArticle(auth()->user(), $this->getRecord())),
        ];
    }

    public function approveComment(int $commentId): void
    {
        $this->updateCommentStatus($commentId, 'approved');
    }

    public function rejectComment(int $commentId): void
    {
        $this->updateCommentStatus($commentId, 'rejected');
    }

    protected function updateCommentStatus(int $commentId, string $status): void
    {
        $comment = Comment::query()
            ->where('article_id', $this->getRecord()->id)
            ->findOrFail($commentId);

        $comment->update(['status' => $status]);

        $notification = Notification::make()
            ->title($status === 'approved' ? 'Comment approved' : 'Comment rejected');

        if ($status === 'approved') {
            $notification->success();
        } else {
            $notification->warning();
        }

        $notification->send();
    }

    /**
     * @return Collection<int, Comment>
     */
    protected function getArticleComments(): Collection
    {
        return $this->getRecord()
            ->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->latest()
            ->get();
    }

    /**
     * @return array{total: int, pending: int, approved: int, rejected: int}
     */
    protected function getCommentCounts(): array
    {
        $counts = Comment::query()
            ->where('article_id', $this->getRecord()->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total'    => (int) $counts->sum(),
            'pending'  => (int) ($counts['pending'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.articles.view-article')
                    ->viewData(fn (): array => [
                        'article'       => $this->getRecord()
                            ->load(['author', 'primaryCategory', 'tags', 'secondaryCategories', 'approvedBy', 'translations']),
                        'preview'       => $this->buildPreviewContext(),
                        'comments'      => $this->getArticleComments(),
                        'commentCounts' => $this->getCommentCounts(),
                    ]),
            ]);
    }

    /**
     * @return array{
     *     locale: string,
     *     dir: string,
     *     title: string,
     *     subtitle: ?string,
     *     excerpt: ?string,
     *     content: ?string,
     *     status_label: string,
     *     labels: array<string, string>
     * }
     */
    protected function buildPreviewContext(): array
    {
        $article = $this->getRecord()->loadMissing('translations');
        $locale = in_array($this->previewLocale, ['ar', 'en'], true) ? $this->previewLocale : 'en';
        $fallback = $locale === 'ar' ? 'en' : 'ar';

        $pick = function (string $field) use ($article, $locale, $fallback): ?string {
            $value = $article->translations->firstWhere('locale', $locale)?->{$field};

            if (filled($value)) {
                return $value;
            }

            return $article->translations->firstWhere('locale', $fallback)?->{$field};
        };

        $labels = $locale === 'ar'
            ? [
                'author'    => 'الكاتب',
                'published' => 'تاريخ النشر',
                'read_time' => 'وقت القراءة',
                'language'  => 'اللغة',
                'minutes'   => 'دقيقة',
                'tags'      => 'الوسوم',
                'categories'=> 'التصنيفات',
                'breaking'  => 'عاجل',
                'empty'     => 'لا يوجد محتوى لهذا المقال بعد.',
            ]
            : [
                'author'    => 'Author',
                'published' => 'Published',
                'read_time' => 'Read time',
                'language'  => 'Language',
                'minutes'   => 'min',
                'tags'      => 'Tags',
                'categories'=> 'Categories',
                'breaking'  => 'Breaking',
                'empty'     => 'This article has no content yet.',
            ];

        $statusLabel = match ($article->status) {
            'published' => $locale === 'ar' ? 'منشور' : 'Published',
            'review', 'under_review' => $locale === 'ar' ? 'قيد المراجعة' : 'Under Review',
            'draft'     => $locale === 'ar' ? 'مسودة' : 'Draft',
            'archived'  => $locale === 'ar' ? 'مؤرشف' : 'Archived',
            'rejected'  => $locale === 'ar' ? 'مرفوض' : 'Rejected',
            'ready'     => $locale === 'ar' ? 'جاهز للنشر' : 'Ready',
            default     => $article->status,
        };

        return [
            'locale'       => $locale,
            'dir'          => $locale === 'ar' ? 'rtl' : 'ltr',
            'title'        => $pick('title') ?? '',
            'subtitle'     => $pick('subtitle'),
            'excerpt'      => $pick('excerpt'),
            'content'      => $pick('content'),
            'status_label' => $statusLabel,
            'labels'       => $labels,
        ];
    }
}
