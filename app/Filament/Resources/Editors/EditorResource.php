<?php

namespace App\Filament\Resources\Editors;

use App\Filament\Resources\Editors\Pages\CreateEditor;
use App\Filament\Resources\Editors\Pages\EditEditor;
use App\Filament\Resources\Editors\Pages\ListEditors;
use App\Filament\Resources\Editors\Schemas\EditorForm;
use App\Filament\Resources\Editors\Tables\EditorsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EditorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Editors';

    protected static ?string $modelLabel = 'Editor';

    protected static ?string $pluralModelLabel = 'Editors';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'editors';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'editor');
    }

    public static function form(Schema $schema): Schema
    {
        return EditorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EditorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEditors::route('/'),
            'create' => CreateEditor::route('/create'),
            'edit'   => EditEditor::route('/{record}/edit'),
        ];
    }
}
