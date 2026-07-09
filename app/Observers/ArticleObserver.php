<?php

namespace App\Observers;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Support\AdminNotifier;
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

        match ($article->status) {
            'ready' => TransactionalMailer::sendToUser($author, 'writer.article_ready', $replace),
            'published' => TransactionalMailer::sendToUser($author, 'writer.article_published', $replace),
            'rejected' => TransactionalMailer::sendToUser($author, 'writer.article_rejected', $replace),
            default => null,
        };
    }
}
