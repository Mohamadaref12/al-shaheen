<?php

namespace App\Observers;

use App\Filament\Resources\ContentSubmissions\ContentSubmissionResource;
use App\Models\ContentSubmission;
use App\Support\AdminNotifier;
use App\Support\AppNotifier;
use App\Support\TransactionalMailer;
use Illuminate\Support\Str;

class ContentSubmissionObserver
{
    /**
     * @var list<string>
     */
    private const REVIEW_STATUSES = ['submitted', 'under_review', 'pending', 'review'];

    public function created(ContentSubmission $submission): void
    {
        $this->notifyIfNeedsReview($submission);
    }

    public function updated(ContentSubmission $submission): void
    {
        if ($submission->wasChanged('status')) {
            $this->notifyIfNeedsReview($submission);
            $this->notifyWriterOfStatusChange($submission);
        }
    }

    private function notifyIfNeedsReview(ContentSubmission $submission): void
    {
        if (! in_array($submission->status, self::REVIEW_STATUSES, true)) {
            return;
        }

        if (! $submission->wasRecentlyCreated && $submission->wasChanged('status')) {
            $previous = (string) $submission->getOriginal('status');

            if (in_array($previous, self::REVIEW_STATUSES, true)) {
                return;
            }
        }

        $title = $submission->title ?: 'Submission #'.$submission->id;

        AdminNotifier::notify(
            AdminNotifier::editorsAndAdmins(auth()->id()),
            'New content submission',
            Str::limit($title, 80),
            ContentSubmissionResource::getUrl('edit', ['record' => $submission]),
            'warning',
            'staff.submission_review',
            ['title' => $title],
        );
    }

    private function notifyWriterOfStatusChange(ContentSubmission $submission): void
    {
        $submission->loadMissing('writer.user');

        $user = $submission->writer?->user;

        if (! $user) {
            return;
        }

        $title = $submission->title ?: 'Submission #'.$submission->id;
        $replace = [
            'title' => $title,
            'name'  => $user->name,
            'notes' => (string) ($submission->reviewer_notes ?? ''),
        ];
        $data = ['submission_id' => (string) $submission->id];

        if ($submission->status === 'approved') {
            TransactionalMailer::sendToUser($user, 'writer.submission_approved', $replace);
            AppNotifier::notifyOne(
                $user,
                'submission_approved',
                'Submission approved',
                Str::limit($title, 100),
                null,
                $data,
            );

            return;
        }

        if ($submission->status === 'rejected') {
            TransactionalMailer::sendToUser($user, 'writer.submission_rejected', $replace);
            AppNotifier::notifyOne(
                $user,
                'submission_rejected',
                'Submission rejected',
                Str::limit($title, 100),
                null,
                $data,
            );
        }
    }
}
