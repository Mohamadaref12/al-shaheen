<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Actions\DownloadArticlePdfAction;
use App\Filament\Actions\TranslateArticleAction;
use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\GuardsLockedContentEdit;
use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Article;
use App\Support\ContentEditability;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditArticle extends EditRecord
{
    use FillsTranslatableFormData;
    use GuardsLockedContentEdit;
    use HasWorkflowHeaderActions;
    use SavesTranslatableFormData;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forArticle(
                fn (): Article => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                TranslateArticleAction::make(),
                DownloadArticlePdfAction::make(),
                Action::make('view')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (): string => ArticleResource::getUrl('view', ['record' => $this->getRecord()])),
                DeleteAction::make(),
            ],
        );
    }

    protected function contentIsEditable(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record instanceof Article
            && ContentEditability::userCanEditArticle(auth()->user(), $record);
    }

    protected function lockedContentMessage(): string
    {
        return ContentEditability::lockedArticleMessage();
    }

    protected function getLockedContentRedirectUrl(int | string $record): string
    {
        return ArticleResource::getUrl('view', ['record' => $record]);
    }
}
