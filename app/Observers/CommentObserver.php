<?php

namespace App\Observers;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use App\Support\AdminNotifier;
use App\Support\TransactionalMailer;
use Illuminate\Support\Str;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        if ($comment->status !== 'pending') {
            return;
        }

        $comment->loadMissing(['article.translations', 'user']);

        $articleTitle = $comment->article?->display_title ?? 'an article';
        $authorName = $comment->user?->name ?? 'A reader';

        AdminNotifier::notify(
            AdminNotifier::editorsAndAdmins(),
            'New comment pending review',
            "{$authorName} commented on \"".Str::limit($articleTitle, 60).'"',
            CommentResource::getUrl('edit', ['record' => $comment]),
            'warning',
            'staff.comment_pending',
            [
                'author' => $authorName,
                'title'  => $articleTitle,
            ],
        );

        if ($comment->user) {
            TransactionalMailer::sendToUser($comment->user, 'reader.comment_pending', [
                'title' => $articleTitle,
                'name'  => $comment->user->name,
            ]);
        }
    }

    public function updated(Comment $comment): void
    {
        if (! $comment->wasChanged('status')) {
            return;
        }

        if (! in_array($comment->status, ['approved', 'rejected'], true)) {
            return;
        }

        $comment->loadMissing(['article.translations', 'user']);

        $user = $comment->user;

        if (! $user) {
            return;
        }

        $articleTitle = $comment->article?->display_title ?? 'an article';
        $replace = [
            'title' => $articleTitle,
            'name'  => $user->name,
        ];

        if ($comment->status === 'approved') {
            TransactionalMailer::sendToUser($user, 'reader.comment_approved', $replace);

            return;
        }

        TransactionalMailer::sendToUser($user, 'reader.comment_rejected', $replace);
    }
}
