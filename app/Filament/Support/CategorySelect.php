<?php

namespace App\Filament\Support;

use App\Models\Category;
use Filament\Forms\Components\Select;

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
                modifyQueryUsing: fn ($query) => $query->with('translations')
            )
        );
    }
}
