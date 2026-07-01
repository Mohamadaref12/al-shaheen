<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sender')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->disabled(),

                        TextInput::make('email')
                            ->label('Email')
                            ->disabled(),

                        TextInput::make('company')
                            ->label('Company')
                            ->disabled(),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->disabled(),

                        TextInput::make('user.name')
                            ->label('Linked User')
                            ->disabled()
                            ->placeholder('Guest'),

                        TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                    ]),

                Section::make('Message')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Subject')
                            ->disabled()
                            ->columnSpanFull(),

                        Textarea::make('message')
                            ->label('Message')
                            ->disabled()
                            ->rows(10)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                'new'     => 'Unread',
                                'read'    => 'Read',
                                'replied' => 'Replied',
                            ])
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Timestamps')
                    ->columns(2)
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Received at')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i') ?? '—'),

                        TextInput::make('updated_at')
                            ->label('Last updated')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i') ?? '—'),
                    ]),
            ])
            ->columns(1);
    }
}
