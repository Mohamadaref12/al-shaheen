<?php

namespace App\Filament\Resources\ContentSubmissions\Pages;

use App\Filament\Resources\ContentSubmissions\ContentSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentSubmissions extends ListRecords
{
    protected static string $resource = ContentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
