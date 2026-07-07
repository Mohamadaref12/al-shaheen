<?php

namespace App\Filament\Resources\Opinions;

use App\Filament\Concerns\SearchesTranslatableTitles;
use App\Filament\Resources\Opinions\Pages\CreateOpinion;
use App\Filament\Resources\Opinions\Pages\EditOpinion;
use App\Filament\Resources\Opinions\Pages\ListOpinions;
use App\Filament\Resources\Opinions\Schemas\OpinionForm;
use App\Filament\Resources\Opinions\Tables\OpinionsTable;
use App\Models\Opinion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OpinionResource extends Resource
{
    use SearchesTranslatableTitles;

    protected static ?string $model = Opinion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Opinions';

    protected static ?string $modelLabel = 'Opinion';

    protected static ?string $pluralModelLabel = 'Opinions';

    protected static ?string $recordTitleAttribute = 'display_title';

    public static function getRecordTitle(?Model $record): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return $record instanceof Opinion
            ? $record->display_title
            : parent::getRecordTitle($record);
    }

    public static function form(Schema $schema): Schema
    {
        return OpinionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpinionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOpinions::route('/'),
            'create' => CreateOpinion::route('/create'),
            'edit'   => EditOpinion::route('/{record}/edit'),
        ];
    }
}
