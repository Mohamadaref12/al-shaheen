<?php

namespace App\Filament\Resources\MediaItems\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\MediaItems\MediaItemResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\MediaItem;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMediaItem extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = MediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forMediaItem(
                fn (): MediaItem => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
