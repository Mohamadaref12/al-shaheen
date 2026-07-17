<?php

namespace App\Filament\Resources\SecondaryCategories\Pages;

use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\SecondaryCategories\SecondaryCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSecondaryCategory extends EditRecord
{
    use FillsTranslatableFormData;
    use SavesTranslatableFormData;

    protected static string $resource = SecondaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
