<?php

namespace App\Filament\Resources\SecondaryCategories;

use App\Filament\Concerns\HasTranslatedLabels;
use App\Filament\Resources\SecondaryCategories\Pages\CreateSecondaryCategory;
use App\Filament\Resources\SecondaryCategories\Pages\EditSecondaryCategory;
use App\Filament\Resources\SecondaryCategories\Pages\ListSecondaryCategories;
use App\Filament\Resources\SecondaryCategories\Schemas\SecondaryCategoryForm;
use App\Filament\Resources\SecondaryCategories\Tables\SecondaryCategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SecondaryCategoryResource extends Resource
{
    use HasTranslatedLabels;

    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    protected static function translationKey(): string
    {
        return 'secondary_categories';
    }

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('parent_id');
    }

    public static function form(Schema $schema): Schema
    {
        return SecondaryCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecondaryCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSecondaryCategories::route('/'),
            'create' => CreateSecondaryCategory::route('/create'),
            'edit'   => EditSecondaryCategory::route('/{record}/edit'),
        ];
    }
}
