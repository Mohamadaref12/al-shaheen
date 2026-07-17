<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Categories\PrimaryCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrimaryCategory extends EditRecord
{
    use FillsTranslatableFormData;
    use SavesTranslatableFormData {
        mutateFormDataBeforeSave as extractTranslationsBeforeSave;
    }

    protected static string $resource = PrimaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->extractTranslationsBeforeSave($data);
        $data['parent_id'] = null;

        return $data;
    }
}
