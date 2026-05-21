<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment Details')
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),

                    Select::make('subscription_id')
                        ->label('Subscription')
                        ->relationship('subscription', 'id')
                        ->required(),

                    TextInput::make('amount')
                        ->numeric()
                        ->required(),

                    TextInput::make('currency')
                        ->default('USD'),

                    TextInput::make('provider')
                        ->required(),

                    TextInput::make('provider_reference'),

                    Select::make('status')
                        ->options([
                            'pending'  => 'Pending',
                            'paid'     => 'Paid',
                            'failed'   => 'Failed',
                            'refunded' => 'Refunded',
                        ])
                        ->required(),

                    DateTimePicker::make('paid_at'),
                ]),
        ])->columns(1);
    }
}
