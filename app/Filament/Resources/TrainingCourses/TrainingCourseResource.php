<?php

namespace App\Filament\Resources\TrainingCourses;

use App\Filament\Resources\TrainingCourses\Pages\CreateTrainingCourse;
use App\Filament\Resources\TrainingCourses\Pages\EditTrainingCourse;
use App\Filament\Resources\TrainingCourses\Pages\ListTrainingCourses;
use App\Filament\Resources\TrainingCourses\Schemas\TrainingCourseForm;
use App\Filament\Resources\TrainingCourses\Tables\TrainingCoursesTable;
use App\Models\TrainingCourse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingCourseResource extends Resource
{
    protected static ?string $model = TrainingCourse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?string $navigationLabel = 'Courses';

    protected static ?string $modelLabel = 'Course';

    protected static ?string $pluralModelLabel = 'Courses';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TrainingCourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingCoursesTable::configure($table);
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
            'index' => ListTrainingCourses::route('/'),
            'create' => CreateTrainingCourse::route('/create'),
            'edit' => EditTrainingCourse::route('/{record}/edit'),
        ];
    }
}
