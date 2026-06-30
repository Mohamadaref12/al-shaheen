<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\DownloadArticlePdfAction;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Comment;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;

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
            DownloadArticlePdfAction::make(),
            EditAction::make(),
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
                        'comments'      => $this->getArticleComments(),
                        'commentCounts' => $this->getCommentCounts(),
                    ]),
            ]);
    }
}
