<?php

namespace App\Support;

class ContentComments
{
    public const ARTICLE_ALLOWS_COMMENTS = true;

    public const NEWS_ALLOWS_COMMENTS = false;

    public static function allowsFor(string $contentType): bool
    {
        return match ($contentType) {
            'article' => self::ARTICLE_ALLOWS_COMMENTS,
            'news'    => self::NEWS_ALLOWS_COMMENTS,
            default   => false,
        };
    }
}
