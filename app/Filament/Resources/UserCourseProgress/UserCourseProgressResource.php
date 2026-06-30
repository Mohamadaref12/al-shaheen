<?php

namespace App\Filament\Resources\UserCourseProgress;

use App\Filament\Resources\UserCourseProgress\Pages\CreateUserCourseProgress;
use App\Filament\Resources\UserCourseProgress\Pages\EditUserCourseProgress;
use App\Filament\Resources\UserCourseProgress\Pages\ListUserCourseProgress;
use App\Filament\Resources\UserCourseProgress\Schemas\UserCourseProgressForm;
use App\Filament\Resources\UserCourseProgress\Tables\UserCourseProgressTable;
use App\Models\UserCourseProgress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserCourseProgressResource extends Resource
{
    protected static ?string $model = UserCourseProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?string $navigationLabel = 'Progress';

    protected static ?string $modelLabel = 'Progress';

    protected static ?string $pluralModelLabel = 'Progress';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return UserCourseProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserCourseProgressTable::configure($table);
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
            'index' => ListUserCourseProgress::route('/'),
            'create' => CreateUserCourseProgress::route('/create'),
            'edit' => EditUserCourseProgress::route('/{record}/edit'),
        ];
    }
}
