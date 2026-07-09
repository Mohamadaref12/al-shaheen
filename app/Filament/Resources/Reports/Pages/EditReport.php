<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Report;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forReport(
                fn (): Report => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
