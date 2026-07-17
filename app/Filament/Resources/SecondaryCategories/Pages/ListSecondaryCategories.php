<?php

namespace App\Filament\Resources\SecondaryCategories\Pages;

use App\Filament\Resources\SecondaryCategories\SecondaryCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSecondaryCategories extends ListRecords
{
    protected static string $resource = SecondaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
