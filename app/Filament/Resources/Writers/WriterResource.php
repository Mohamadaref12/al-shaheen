<?php

namespace App\Filament\Resources\Writers;

use App\Filament\Resources\Writers\Pages\CreateWriter;
use App\Filament\Resources\Writers\Pages\EditWriter;
use App\Filament\Resources\Writers\Pages\ListWriters;
use App\Filament\Resources\Writers\Schemas\WriterForm;
use App\Filament\Resources\Writers\Tables\WritersTable;
use App\Models\Writer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WriterResource extends Resource
{
    protected static ?string $model = Writer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Writers';

    protected static ?string $modelLabel = 'Writer';

    protected static ?string $pluralModelLabel = 'Writers';

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function form(Schema $schema): Schema
    {
        return WriterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WritersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWriters::route('/'),
            'create' => CreateWriter::route('/create'),
            'edit'   => EditWriter::route('/{record}/edit'),
        ];
    }
}