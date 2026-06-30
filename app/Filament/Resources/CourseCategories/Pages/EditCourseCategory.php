<?php

namespace App\Filament\Resources\CourseCategories\Pages;

use App\Filament\Resources\CourseCategories\CourseCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditCourseCategory extends EditRecord
{
    protected static string $resource = CourseCategoryResource::class;
}
