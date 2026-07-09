<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Comment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComment extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forComment(
                fn (): Comment => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
