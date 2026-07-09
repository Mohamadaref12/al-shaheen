<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use App\Support\TransactionalMailer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->status === 'new') {
            $this->record->update(['status' => 'read']);
            $this->record->refresh();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markReplied')
                ->label('Mark as Replied')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (ContactMessage $record): bool => $record->status !== 'replied')
                ->schema([
                    Textarea::make('reply_message')
                        ->label('Reply message')
                        ->rows(5)
                        ->required()
                        ->maxLength(5000),
                ])
                ->action(function (ContactMessage $record, array $data): void {
                    $record->update(['status' => 'replied']);

                    TransactionalMailer::send(
                        email: $record->email,
                        name: $record->name,
                        locale: 'ar',
                        key: 'reader.contact_replied',
                        replace: [
                            'name'    => $record->name,
                            'subject' => $record->subject,
                            'reply'   => $data['reply_message'],
                        ],
                    );

                    Notification::make()
                        ->title('Message marked as replied')
                        ->success()
                        ->send();
                }),

            Action::make('markUnread')
                ->label('Mark as Unread')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->visible(fn (ContactMessage $record): bool => $record->status !== 'new')
                ->action(function (ContactMessage $record): void {
                    $record->update(['status' => 'new']);

                    Notification::make()
                        ->title('Message marked as unread')
                        ->warning()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
