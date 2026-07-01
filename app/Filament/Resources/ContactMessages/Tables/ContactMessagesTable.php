<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn ($record): string => \App\Filament\Resources\ContactMessages\ContactMessageResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight(fn ($record): FontWeight => $record->status === 'new'
                        ? FontWeight::Bold
                        : FontWeight::Medium),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('company')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record): string => $record->subject),

                TextColumn::make('message')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new'     => 'Unread',
                        'read'    => 'Read',
                        'replied' => 'Replied',
                        default   => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new'     => 'warning',
                        'read'    => 'gray',
                        'replied' => 'success',
                        default   => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new'     => 'Unread',
                        'read'    => 'Read',
                        'replied' => 'Replied',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
