<?php

namespace App\Filament\Resources\ContentSubmissions\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\ContentSubmissions\ContentSubmissionResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\ContentSubmission;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentSubmission extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = ContentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forContentSubmission(
                fn (): ContentSubmission => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
