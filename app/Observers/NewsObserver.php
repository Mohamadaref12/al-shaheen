<?php

namespace App\Observers;

use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use App\Support\AdminNotifier;
use App\Support\AppNotifier;
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
        $news->loadMissing('author', 'translations');
        $author = $news->author;

        if (! $author) {
            return;
        }

        $title = $news->localizedDisplayValue('title', 'News #'.$news->id);
        $replace = [
            'title' => $title,
            'name'  => $author->name,
        ];

        if ($news->status === 'published') {
            TransactionalMailer::sendToUser($author, 'writer.news_published', $replace);

            AppNotifier::notifyOne(
                $author,
                'news_published',
                'News published',
                Str::limit($title, 100),
                null,
                ['news_id' => (string) $news->id],
            );

            AppNotifier::broadcast(
                'news',
                'news_published',
                'Breaking on Al Shaheen 360',
                Str::limit($title, 100),
                url('/'),
                ['news_id' => (string) $news->id],
            );

            return;
        }

        if ($news->status === 'rejected') {
            AppNotifier::notifyOne(
                $author,
                'news_rejected',
                'News rejected',
                Str::limit($title, 100),
                null,
                ['news_id' => (string) $news->id],
            );
        }
    }
}
