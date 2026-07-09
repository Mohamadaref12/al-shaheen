<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Actions\DownloadNewsPdfAction;
use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\News;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    use FillsTranslatableFormData;
    use HasWorkflowHeaderActions;
    use SavesTranslatableFormData;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forNews(
                fn (): News => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DownloadNewsPdfAction::make(),
                DeleteAction::make(),
            ],
        );
    }
}
