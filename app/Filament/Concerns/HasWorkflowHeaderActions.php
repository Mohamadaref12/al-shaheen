<?php

namespace App\Filament\Concerns;

trait HasWorkflowHeaderActions
{
    /**
     * @param  array<int, \Filament\Actions\Action|\Filament\Actions\ActionGroup>  $workflowActions
     * @param  array<int, \Filament\Actions\Action|\Filament\Actions\ActionGroup>  $actions
     * @return array<int, \Filament\Actions\Action|\Filament\Actions\ActionGroup>
     */
    protected function mergeHeaderActions(array $workflowActions, array $actions): array
    {
        return [
            ...$workflowActions,
            ...$actions,
        ];
    }

    protected function refreshWorkflowForm(): void
    {
        $this->record->refresh();
        $this->fillForm();
    }
}
