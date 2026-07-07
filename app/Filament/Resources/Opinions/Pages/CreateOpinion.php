<?php

namespace App\Filament\Resources\Opinions\Pages;

use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Opinions\OpinionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpinion extends CreateRecord
{
    use SavesTranslatableFormData;

    protected static string $resource = OpinionResource::class;
}
