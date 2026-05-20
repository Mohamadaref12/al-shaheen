<?php

namespace App\Filament\Resources\Readers\Pages;

use App\Filament\Resources\Readers\ReaderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReader extends EditRecord
{
    protected static string $resource = ReaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
