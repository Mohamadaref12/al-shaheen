<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    use SavesTranslatableFormData;

    protected static string $resource = ArticleResource::class;
}
