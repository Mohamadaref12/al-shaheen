<?php

namespace App\Observers;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use App\Support\AdminNotifier;
use App\Support\AppNotifier;
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

            AppNotifier::notifyOne(
                $comment->user,
                'comment_pending',
                'Comment submitted',
                'Your comment on "'.Str::limit($articleTitle, 60).'" is pending review.',
                null,
                [
                    'comment_id' => (string) $comment->id,
                    'article_id' => (string) ($comment->article_id ?? ''),
                ],
            );
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
        $data = [
            'comment_id' => (string) $comment->id,
            'article_id' => (string) ($comment->article_id ?? ''),
        ];

        if ($comment->status === 'approved') {
            TransactionalMailer::sendToUser($user, 'reader.comment_approved', $replace);

            AppNotifier::notifyOne(
                $user,
                'comment_approved',
                'Comment approved',
                'Your comment on "'.Str::limit($articleTitle, 60).'" was approved.',
                null,
                $data,
            );

            return;
        }

        TransactionalMailer::sendToUser($user, 'reader.comment_rejected', $replace);

        AppNotifier::notifyOne(
            $user,
            'comment_rejected',
            'Comment rejected',
            'Your comment on "'.Str::limit($articleTitle, 60).'" was rejected.',
            null,
            $data,
        );
    }
}
