<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\NewsletterSubscriber;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterSubscriber extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forNewsletterSubscriber(
                fn (): NewsletterSubscriber => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
