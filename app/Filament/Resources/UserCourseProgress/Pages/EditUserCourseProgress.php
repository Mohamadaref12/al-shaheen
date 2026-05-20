<?php

namespace App\Filament\Resources\UserCourseProgress\Pages;

use App\Filament\Resources\UserCourseProgress\UserCourseProgressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserCourseProgress extends EditRecord
{
    protected static string $resource = UserCourseProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
