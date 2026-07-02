<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UnreadContactMessagesWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Unread Contact Messages')
            ->description('Messages from the contact form awaiting review')
            ->query(fn (): Builder => ContactMessage::query()
                ->unread()
                ->latest())
            ->paginated([5])
            ->emptyStateHeading('No unread messages')
            ->emptyStateDescription('New contact form submissions will appear here.')
            ->columns([
                TextColumn::make('name')
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('email')
                    ->copyable(),

                TextColumn::make('subject')
                    ->limit(45)
                    ->tooltip(fn (ContactMessage $record): string => $record->subject),

                TextColumn::make('message')
                    ->limit(60)
                    ->tooltip(fn (ContactMessage $record): string => $record->message),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->since(),
            ])
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
