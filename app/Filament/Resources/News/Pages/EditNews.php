<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Actions\DownloadNewsPdfAction;
use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\GuardsLockedContentEdit;
use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\News;
use App\Support\ContentEditability;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    use FillsTranslatableFormData;
    use GuardsLockedContentEdit;
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

    protected function contentIsEditable(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record instanceof News
            && ContentEditability::userCanEditNews(auth()->user(), $record);
    }

    protected function lockedContentMessage(): string
    {
        return ContentEditability::lockedNewsMessage();
    }

    protected function getLockedContentRedirectUrl(int | string $record): string
    {
        return NewsResource::getUrl('index');
    }
}
