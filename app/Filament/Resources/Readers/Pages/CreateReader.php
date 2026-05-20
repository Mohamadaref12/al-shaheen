<?php

namespace App\Filament\Resources\Readers\Pages;

use App\Filament\Resources\Readers\ReaderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReader extends CreateRecord
{
    protected static string $resource = ReaderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'reader';
        return $data;
    }
}
