<?php

namespace App\Observers;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Support\AdminNotifier;
use App\Support\AppNotifier;
use App\Support\TransactionalMailer;
use Illuminate\Support\Str;

class ArticleObserver
{
    public function created(Article $article): void
    {
        $this->notifyIfNeedsReview($article);
    }

    public function updated(Article $article): void
    {
        if (! $article->wasChanged('status')) {
            return;
        }

        $this->notifyIfNeedsReview($article);
        $this->notifyAuthorOfStatusChange($article);
    }

    private function notifyIfNeedsReview(Article $article): void
    {
        if (! in_array($article->status, ArticleResource::AWAITING_APPROVAL_STATUSES, true)) {
            return;
        }

        $title = $article->display_title ?? 'Article #'.$article->id;

        AdminNotifier::notify(
            AdminNotifier::editorsAndAdmins(auth()->id()),
            'Article awaiting review',
            Str::limit($title, 80),
            ArticleResource::getUrl('edit', ['record' => $article]),
            'warning',
            'staff.article_review',
            ['title' => $title],
        );
    }

    private function notifyAuthorOfStatusChange(Article $article): void
    {
        $article->loadMissing('author', 'translations');

        $author = $article->author;

        if (! $author) {
            return;
        }

        $title = $article->localizedDisplayValue('title', 'Article #'.$article->id);
        $replace = [
            'title' => $title,
            'name'  => $author->name,
            'notes' => (string) ($article->writer_notes ?? ''),
        ];
        $data = ['article_id' => (string) $article->id];

        match ($article->status) {
            'ready' => $this->notifyAuthor(
                $author,
                'article_ready',
                'Article approved',
                Str::limit($title, 100),
                $replace,
                'writer.article_ready',
                $data,
            ),
            'published' => $this->notifyPublished($article, $author, $title, $replace),
            'rejected' => $this->notifyAuthor(
                $author,
                'article_rejected',
                'Article rejected',
                Str::limit($title, 100),
                $replace,
                'writer.article_rejected',
                $data,
            ),
            default => null,
        };
    }

    private function notifyPublished(Article $article, $author, string $title, array $replace): void
    {
        $this->notifyAuthor(
            $author,
            'article_published',
            'Article published',
            Str::limit($title, 100),
            $replace,
            'writer.article_published',
            ['article_id' => (string) $article->id],
        );

        AppNotifier::broadcast(
            'articles',
            'article_published',
            'New on Al Shaheen 360',
            Str::limit($title, 100),
            url('/'),
            ['article_id' => (string) $article->id],
        );
    }

    /**
     * @param  array<string, string>  $replace
     * @param  array<string, mixed>  $data
     */
    private function notifyAuthor(
        $author,
        string $type,
        string $title,
        string $body,
        array $replace,
        string $mailKey,
        array $data = [],
    ): void {
        TransactionalMailer::sendToUser($author, $mailKey, $replace);

        AppNotifier::notifyOne(
            $author,
            $type,
            $title,
            $body,
            null,
            $data,
        );
    }
}
