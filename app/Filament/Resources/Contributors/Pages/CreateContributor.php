<?php

namespace App\Filament\Resources\Contributors\Pages;

use App\Filament\Resources\Contributors\ContributorResource;
use App\Models\Contributor;
use Filament\Resources\Pages\CreateRecord;

class CreateContributor extends CreateRecord
{
    protected static string $resource = ContributorResource::class;

    protected function afterCreate(): void
    {
        Contributor::create(['user_id' => $this->record->id]);
    }
}
