<?php

namespace App\Filament\Resources\ContentSubmissions\Pages;

use App\Filament\Resources\ContentSubmissions\ContentSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentSubmission extends EditRecord
{
    protected static string $resource = ContentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
