<?php

namespace App\Filament\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

trait GuardsLockedContentEdit
{
    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->contentIsEditable($this->getRecord())) {
            return;
        }

        Notification::make()
            ->title('Editing locked')
            ->body($this->lockedContentMessage())
            ->warning()
            ->send();

        $this->redirect($this->getLockedContentRedirectUrl($record));
    }

    abstract protected function contentIsEditable(Model $record): bool;

    abstract protected function lockedContentMessage(): string;

    abstract protected function getLockedContentRedirectUrl(int | string $record): string;
}
