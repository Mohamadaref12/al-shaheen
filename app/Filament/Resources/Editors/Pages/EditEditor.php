<?php

namespace App\Filament\Resources\Editors\Pages;

use App\Filament\Resources\Editors\EditorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEditor extends EditRecord
{
    protected static string $resource = EditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
