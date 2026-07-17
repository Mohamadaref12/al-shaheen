<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\PrimaryCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrimaryCategories extends ListRecords
{
    protected static string $resource = PrimaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
