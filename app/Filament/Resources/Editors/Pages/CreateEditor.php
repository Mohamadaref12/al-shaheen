<?php

namespace App\Filament\Resources\Editors\Pages;

use App\Filament\Resources\Editors\EditorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEditor extends CreateRecord
{
    protected static string $resource = EditorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'editor';
        return $data;
    }
}
