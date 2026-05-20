<?php

namespace App\Filament\Resources\Editors\Pages;

use App\Filament\Resources\Editors\EditorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEditors extends ListRecords
{
    protected static string $resource = EditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
