<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\News\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    use SavesTranslatableFormData;

    protected static string $resource = NewsResource::class;
}
