<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LocalizedTitleColumn
{
    public static function make(string $labelKey = 'title', ?int $limit = null): TextColumn
    {
        $column = TextColumn::make('display_title')
            ->label(__("filament.fields.{$labelKey}"))
            ->description(fn (Model $record): ?string => self::alternateTitle($record))
            ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                'translations',
                fn (Builder $q) => $q->where('title', 'like', "%{$search}%")
            ))
            ->extraCellAttributes(['dir' => 'auto']);

        if ($limit !== null) {
            $column->limit($limit);
        }

        return $column;
    }

    public static function alternateTitle(Model $record): ?string
    {
        if (! method_exists($record, 'getTranslatedAttribute') || ! method_exists($record, 'localizedDisplayValue')) {
            return null;
        }

        $locale = in_array(app()->getLocale(), ['ar', 'en'], true)
            ? app()->getLocale()
            : (string) config('app.fallback_locale', 'en');

        $alternate = $locale === 'ar' ? 'en' : 'ar';
        $value = $record->getTranslatedAttribute('title', $alternate);

        if (! filled($value)) {
            return null;
        }

        $primary = $record->localizedDisplayValue('title', '');

        return (string) $value !== $primary ? (string) $value : null;
    }
}
