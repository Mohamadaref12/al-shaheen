<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription Details')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('package_id')
                            ->label('Package')
                            ->relationship('package', 'name')
                            ->searchable(),

                        TextInput::make('plan')
                            ->maxLength(100),

                        Select::make('status')
                            ->options([
                                'active'    => 'Active',
                                'expired'   => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('active'),

                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->required(),

                        DateTimePicker::make('ends_at')
                            ->label('Ends At'),
                    ]),
            ]);
    }
}
