<?php

namespace App\Observers;

use App\Filament\Resources\Opinions\OpinionResource;
use App\Models\Opinion;
use App\Support\AdminNotifier;
use App\Support\AppNotifier;
use App\Support\TransactionalMailer;
use Illuminate\Support\Str;

class OpinionObserver
{
    public function created(Opinion $opinion): void
    {
        $this->notifyIfNeedsReview($opinion);
    }

    public function updated(Opinion $opinion): void
    {
        if (! $opinion->wasChanged('status')) {
            return;
        }

        $this->notifyIfNeedsReview($opinion);
        $this->notifyAuthorOfStatusChange($opinion);
    }

    private function notifyIfNeedsReview(Opinion $opinion): void
    {
        if ($opinion->status !== 'under_review') {
            return;
        }

        $title = $opinion->display_title ?? 'Opinion #'.$opinion->id;

        AdminNotifier::notify(
            AdminNotifier::editorsAndAdmins(auth()->id()),
            'Opinion awaiting review',
            Str::limit($title, 80),
            OpinionResource::getUrl('edit', ['record' => $opinion]),
            'warning',
            'staff.opinion_review',
            ['title' => $title],
        );
    }

    private function notifyAuthorOfStatusChange(Opinion $opinion): void
    {
        $opinion->loadMissing('author', 'translations');
        $author = $opinion->author;

        if (! $author) {
            return;
        }

        $title = $opinion->localizedDisplayValue('title', 'Opinion #'.$opinion->id);
        $replace = [
            'title' => $title,
            'name'  => $author->name,
        ];

        if ($opinion->status === 'published') {
            TransactionalMailer::sendToUser($author, 'writer.opinion_published', $replace);

            AppNotifier::notifyOne(
                $author,
                'opinion_published',
                'Opinion published',
                Str::limit($title, 100),
                null,
                ['opinion_id' => (string) $opinion->id],
            );

            AppNotifier::broadcast(
                'opinions',
                'opinion_published',
                'New opinion on Al Shaheen 360',
                Str::limit($title, 100),
                url('/'),
                ['opinion_id' => (string) $opinion->id],
            );

            return;
        }

        if ($opinion->status === 'rejected') {
            AppNotifier::notifyOne(
                $author,
                'opinion_rejected',
                'Opinion rejected',
                Str::limit($title, 100),
                null,
                ['opinion_id' => (string) $opinion->id],
            );
        }
    }
}
