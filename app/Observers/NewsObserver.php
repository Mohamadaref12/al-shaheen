<?php

namespace App\Observers;

use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use App\Support\AdminNotifier;
use App\Support\TransactionalMailer;
use Illuminate\Support\Str;

class NewsObserver
{
    public function created(News $news): void
    {
        $this->notifyIfNeedsReview($news);
    }

    public function updated(News $news): void
    {
        if (! $news->wasChanged('status')) {
            return;
        }

        $this->notifyIfNeedsReview($news);
        $this->notifyAuthorOfStatusChange($news);
    }

    private function notifyIfNeedsReview(News $news): void
    {
        if ($news->status !== 'under_review') {
            return;
        }

        $title = $news->display_title ?? 'News #'.$news->id;

        AdminNotifier::notify(
            AdminNotifier::editorsAndAdmins(auth()->id()),
            'News awaiting review',
            Str::limit($title, 80),
            NewsResource::getUrl('edit', ['record' => $news]),
            'warning',
            'staff.news_review',
            ['title' => $title],
        );
    }

    private function notifyAuthorOfStatusChange(News $news): void
    {
        if ($news->status !== 'published') {
            return;
        }

        $news->loadMissing('author', 'translations');

        $author = $news->author;

        if (! $author) {
            return;
        }

        TransactionalMailer::sendToUser($author, 'writer.news_published', [
            'title' => $news->localizedDisplayValue('title', 'News #'.$news->id),
            'name'  => $author->name,
        ]);
    }
}
