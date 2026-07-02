<?php

namespace App\Services\Ai;

use App\Contracts\NewsImprovementService;

class NullNewsImprovementService implements NewsImprovementService
{
    public function isAvailable(): bool
    {
        return false;
    }

    public function improve(array $snapshot, string $focus = 'all'): array
    {
        return [
            'suggestions' => [],
            'notes'       => [],
            'provider'    => null,
            'model'       => null,
        ];
    }
}
