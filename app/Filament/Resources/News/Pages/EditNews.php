<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\News\NewsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    use FillsTranslatableFormData;
    use SavesTranslatableFormData;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
