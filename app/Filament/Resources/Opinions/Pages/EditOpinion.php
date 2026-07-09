<?php

namespace App\Filament\Resources\Opinions\Pages;

use App\Filament\Concerns\FillsTranslatableFormData;
use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Concerns\SavesTranslatableFormData;
use App\Filament\Resources\Opinions\OpinionResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Opinion;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpinion extends EditRecord
{
    use FillsTranslatableFormData;
    use HasWorkflowHeaderActions;
    use SavesTranslatableFormData;

    protected static string $resource = OpinionResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forOpinion(
                fn (): Opinion => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
