<?php

namespace App\Filament\Concerns;

trait SearchesTranslatableTitles
{
    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            static::getTranslatableSearchRelation() . '.' . static::getTranslatableSearchColumn(),
        ];
    }

    protected static function getTranslatableSearchRelation(): string
    {
        return 'translations';
    }

    protected static function getTranslatableSearchColumn(): string
    {
        return 'title';
    }
}
