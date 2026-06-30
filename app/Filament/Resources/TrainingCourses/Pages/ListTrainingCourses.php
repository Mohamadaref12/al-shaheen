<?php

namespace App\Filament\Resources\TrainingCourses\Pages;

use App\Filament\Resources\CourseCategories\CourseCategoryResource;
use App\Filament\Resources\TrainingCourses\TrainingCourseResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListTrainingCourses extends ListRecords
{
    protected static string $resource = TrainingCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('categories')
                ->label('Categories')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(CourseCategoryResource::getUrl('index')),

            CreateAction::make(),
        ];
    }
}
