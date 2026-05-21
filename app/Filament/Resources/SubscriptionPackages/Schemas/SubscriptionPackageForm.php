<?php

namespace App\Filament\Resources\SubscriptionPackages\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('price')
                            ->numeric()
                            ->prefix('USD')
                            ->required(),

                        TextInput::make('duration_days')
                            ->label('Duration (days)')
                            ->numeric()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Repeater::make('features')
                            ->label('Features')
                            ->simple(
                                TextInput::make('feature')->required()
                            )
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
