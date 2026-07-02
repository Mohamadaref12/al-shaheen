<?php

namespace App\Services\Ai;

use App\Contracts\ArticleTranslationService;

class NullArticleTranslationService implements ArticleTranslationService
{
    public function isAvailable(): bool
    {
        return false;
    }

    public function translate(array $snapshot): array
    {
        return [
            'suggestions' => [],
            'notes'       => [],
            'provider'    => null,
            'model'       => null,
        ];
    }
}
