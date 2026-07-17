<?php

namespace App\Filament\Resources\SecondaryCategories\Pages;

use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\SecondaryCategories\SecondaryCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSecondaryCategory extends CreateRecord
{
    use SavesTranslatableFormData;

    protected static string $resource = SecondaryCategoryResource::class;
}
