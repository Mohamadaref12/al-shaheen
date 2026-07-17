<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Concerns\HasTranslatedLabels;
use App\Filament\Resources\Categories\Pages\CreatePrimaryCategory;
use App\Filament\Resources\Categories\Pages\EditPrimaryCategory;
use App\Filament\Resources\Categories\Pages\ListPrimaryCategories;
use App\Filament\Resources\Categories\Schemas\PrimaryCategoryForm;
use App\Filament\Resources\Categories\Tables\PrimaryCategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrimaryCategoryResource extends Resource
{
    use HasTranslatedLabels;

    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static function translationKey(): string
    {
        return 'primary_categories';
    }

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('parent_id');
    }

    public static function form(Schema $schema): Schema
    {
        return PrimaryCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrimaryCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPrimaryCategories::route('/'),
            'create' => CreatePrimaryCategory::route('/create'),
            'edit'   => EditPrimaryCategory::route('/{record}/edit'),
        ];
    }
}
