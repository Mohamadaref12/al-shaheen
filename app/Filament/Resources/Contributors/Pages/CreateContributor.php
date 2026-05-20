<?php

namespace App\Filament\Resources\Contributors\Pages;

use App\Filament\Resources\Contributors\ContributorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContributor extends CreateRecord
{
    protected static string $resource = ContributorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'contributor';
        return $data;
    }
}
