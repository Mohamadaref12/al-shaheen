<?php

namespace App\Filament\Support;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

class CategorySelect
{
    public static function configure(Select $select): Select
    {
        return $select
            ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->display_name);
    }

    public static function relationship(Select $select, string $relationship): Select
    {
        return self::configure(
            $select->relationship(
                $relationship,
                modifyQueryUsing: fn (Builder $query): Builder => $query
                    ->with('translations')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
            )
        );
    }

    public static function primary(Select $select): Select
    {
        return self::configure(
            $select
                ->relationship(
                    'primaryCategory',
                    modifyQueryUsing: fn (Builder $query): Builder => $query
                        ->with('translations')
                        ->whereNull('parent_id')
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                )
                ->helperText('Main section for this article (top-level category).')
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('secondaryCategories', []))
        );
    }

    public static function secondary(Select $select): Select
    {
        return self::configure(
            $select
                ->relationship(
                    'secondaryCategories',
                    modifyQueryUsing: function (Builder $query, Get $get): Builder {
                        $query
                            ->with('translations')
                            ->whereNotNull('parent_id')
                            ->where('is_active', true)
                            ->orderBy('sort_order');

                        if ($primaryId = $get('primary_category_id')) {
                            return $query->where('parent_id', $primaryId);
                        }

                        return $query->whereRaw('0 = 1');
                    }
                )
                ->helperText(fn (Get $get): string => blank($get('primary_category_id'))
                    ? 'Select a primary category first, then choose sub-topics.'
                    : 'Optional sub-topics under the selected primary category.')
                ->disabled(fn (Get $get): bool => blank($get('primary_category_id')))
                ->dehydrated()
        );
    }

    public static function topLevel(Select $select, string $relationship): Select
    {
        return self::configure(
            $select->relationship(
                $relationship,
                modifyQueryUsing: fn (Builder $query): Builder => $query
                    ->with('translations')
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
            )
        );
    }
}
