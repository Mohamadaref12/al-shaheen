<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Concerns\HasWorkflowHeaderActions;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Support\ContentStatusActions;
use App\Models\Payment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    use HasWorkflowHeaderActions;

    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return $this->mergeHeaderActions(
            ContentStatusActions::forPayment(
                fn (): Payment => $this->getRecord(),
                fn () => $this->refreshWorkflowForm(),
            ),
            [
                DeleteAction::make(),
            ],
        );
    }
}
