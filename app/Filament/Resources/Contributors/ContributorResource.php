<?php

namespace App\Filament\Resources\Contributors;

use App\Filament\Resources\Contributors\Pages\CreateContributor;
use App\Filament\Resources\Contributors\Pages\EditContributor;
use App\Filament\Resources\Contributors\Pages\ListContributors;
use App\Filament\Resources\Contributors\Schemas\ContributorForm;
use App\Filament\Resources\Contributors\Tables\ContributorsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContributorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Contributors';

    protected static ?string $modelLabel = 'Contributor';

    protected static ?string $pluralModelLabel = 'Contributors';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'contributors';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'contributor');
    }

    public static function form(Schema $schema): Schema
    {
        return ContributorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContributorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListContributors::route('/'),
            'create' => CreateContributor::route('/create'),
            'edit'   => EditContributor::route('/{record}/edit'),
        ];
    }
}
