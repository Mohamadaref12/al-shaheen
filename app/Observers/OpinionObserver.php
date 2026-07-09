<?php

namespace App\Observers;

use App\Filament\Resources\Opinions\OpinionResource;
use App\Models\Opinion;
use App\Support\AdminNotifier;
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
        if ($opinion->status !== 'published') {
            return;
        }

        $opinion->loadMissing('author', 'translations');

        $author = $opinion->author;

        if (! $author) {
            return;
        }

        TransactionalMailer::sendToUser($author, 'writer.opinion_published', [
            'title' => $opinion->localizedDisplayValue('title', 'Opinion #'.$opinion->id),
            'name'  => $author->name,
        ]);
    }
}
