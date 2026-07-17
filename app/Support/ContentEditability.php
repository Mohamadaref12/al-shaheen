<?php

namespace App\Support;

use App\Models\Article;
use App\Models\News;
use App\Models\User;

class ContentEditability
{
    /**
     * @var list<string>
     */
    public const ARTICLE_WRITER_EDITABLE_STATUSES = [
        'draft',
        'submitted',
        'under_review',
        'review',
        'rejected',
    ];

    /**
     * @var list<string>
     */
    public const ARTICLE_LOCKED_STATUSES = [
        'ready',
        'scheduled',
        'published',
        'archived',
    ];

    /**
     * @var list<string>
     */
    public const NEWS_WRITER_EDITABLE_STATUSES = [
        'draft',
        'under_review',
        'rejected',
    ];

    /**
     * @var list<string>
     */
    public const NEWS_LOCKED_STATUSES = [
        'published',
        'archived',
    ];

    public static function writerCanEditArticle(Article $article): bool
    {
        return in_array($article->status, self::ARTICLE_WRITER_EDITABLE_STATUSES, true);
    }

    public static function writerCanEditNews(News $news): bool
    {
        return in_array($news->status, self::NEWS_WRITER_EDITABLE_STATUSES, true);
    }

    public static function writerCanEditArticleStatus(string $status): bool
    {
        return in_array($status, self::ARTICLE_WRITER_EDITABLE_STATUSES, true);
    }

    public static function writerCanEditNewsStatus(string $status): bool
    {
        return in_array($status, self::NEWS_WRITER_EDITABLE_STATUSES, true);
    }

    public static function userCanBypassEditLock(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->editor()->exists() || $user->admin()->exists();
    }

    public static function userCanEditArticle(?User $user, Article $article): bool
    {
        if (self::userCanBypassEditLock($user)) {
            return true;
        }

        return self::writerCanEditArticle($article);
    }

    public static function userCanEditNews(?User $user, News $news): bool
    {
        if (self::userCanBypassEditLock($user)) {
            return true;
        }

        return self::writerCanEditNews($news);
    }

    public static function articleIsEditableForUser(?User $user, Article $article): bool
    {
        return self::userCanEditArticle($user, $article);
    }

    public static function newsIsEditableForUser(?User $user, News $news): bool
    {
        return self::userCanEditNews($user, $news);
    }

    public static function lockedArticleMessage(): string
    {
        return 'This article can no longer be edited after approval. It becomes editable again only when rejected.';
    }

    public static function lockedNewsMessage(): string
    {
        return 'This news item can no longer be edited after approval. It becomes editable again only when rejected.';
    }
}
