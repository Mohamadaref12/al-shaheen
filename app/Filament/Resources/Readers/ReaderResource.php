<?php

namespace App\Filament\Resources\Readers;

use App\Filament\Resources\Readers\Pages\CreateReader;
use App\Filament\Resources\Readers\Pages\EditReader;
use App\Filament\Resources\Readers\Pages\ListReaders;
use App\Filament\Resources\Readers\Schemas\ReaderForm;
use App\Filament\Resources\Readers\Tables\ReadersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReaderResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Readers';

    protected static ?string $modelLabel = 'Reader';

    protected static ?string $pluralModelLabel = 'Readers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'readers';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('reader');
    }

    public static function form(Schema $schema): Schema
    {
        return ReaderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReaders::route('/'),
            'create' => CreateReader::route('/create'),
            'edit'   => EditReader::route('/{record}/edit'),
        ];
    }
}
