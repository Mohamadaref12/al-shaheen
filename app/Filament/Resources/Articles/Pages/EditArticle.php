<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\DownloadArticlePdfAction;
use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditArticle extends EditRecord
{
    use FillsTranslatableFormData;
    use SavesTranslatableFormData;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DownloadArticlePdfAction::make(),
            Action::make('view')
                ->label('Preview')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn (): string => ArticleResource::getUrl('view', ['record' => $this->getRecord()])),
            DeleteAction::make(),
        ];
    }
}
