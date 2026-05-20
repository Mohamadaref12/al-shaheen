<?php

namespace App\Filament\Resources\UserCourseProgress\Pages;

use App\Filament\Resources\UserCourseProgress\UserCourseProgressResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserCourseProgress extends ListRecords
{
    protected static string $resource = UserCourseProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
