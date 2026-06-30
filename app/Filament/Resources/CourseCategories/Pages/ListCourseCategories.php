<?php

namespace App\Filament\Resources\CourseCategories\Pages;

use App\Filament\Resources\CourseCategories\CourseCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCourseCategories extends ListRecords
{
    protected static string $resource = CourseCategoryResource::class;
}
