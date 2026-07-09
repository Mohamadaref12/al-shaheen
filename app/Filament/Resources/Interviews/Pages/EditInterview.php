<?php

namespace App\Filament\Resources\Interviews\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\Interviews\InterviewResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Interview;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInterview extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = InterviewResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forInterview(
                fn (): Interview => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
