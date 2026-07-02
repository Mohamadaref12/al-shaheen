<?php

namespace App\Services\Ai;

use App\Contracts\NewsTranslationService;

class NullNewsTranslationService implements NewsTranslationService
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
