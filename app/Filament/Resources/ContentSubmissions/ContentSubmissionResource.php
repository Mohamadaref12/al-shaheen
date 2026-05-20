<?php

namespace App\Filament\Resources\ContentSubmissions;

use App\Filament\Resources\ContentSubmissions\Pages\CreateContentSubmission;
use App\Filament\Resources\ContentSubmissions\Pages\EditContentSubmission;
use App\Filament\Resources\ContentSubmissions\Pages\ListContentSubmissions;
use App\Filament\Resources\ContentSubmissions\Schemas\ContentSubmissionForm;
use App\Filament\Resources\ContentSubmissions\Tables\ContentSubmissionsTable;
use App\Models\ContentSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentSubmissionResource extends Resource
{
    protected static ?string $model = ContentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Submissions';

    protected static ?string $modelLabel = 'Submission';

    protected static ?string $pluralModelLabel = 'Submissions';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ContentSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentSubmissions::route('/'),
            'create' => CreateContentSubmission::route('/create'),
            'edit' => EditContentSubmission::route('/{record}/edit'),
        ];
    }
}
