<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Categories\PrimaryCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrimaryCategory extends CreateRecord
{
    use SavesTranslatableFormData {
        mutateFormDataBeforeCreate as extractTranslationsBeforeCreate;
    }

    protected static string $resource = PrimaryCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->extractTranslationsBeforeCreate($data);
        $data['parent_id'] = null;

        return $data;
    }
}
