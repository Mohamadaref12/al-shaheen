<?php

namespace App\Contracts;

interface NewsTranslationService
{
    public function isAvailable(): bool;

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array{
     *     suggestions: array<string, mixed>,
     *     notes: array<int, array<string, string>>,
     *     provider: string|null,
     *     model: string|null
     * }
     */
    public function translate(array $snapshot): array;
}
