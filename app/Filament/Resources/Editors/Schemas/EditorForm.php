<?php

namespace App\Filament\Resources\Editors\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation) => $operation === 'create'),
                    ]),

                Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        Select::make('locale')
                            ->options(['ar' => 'Arabic', 'en' => 'English'])
                            ->required()
                            ->default('ar'),

                        TextInput::make('country')
                            ->maxLength(100),

                        TextInput::make('language')
                            ->maxLength(50),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),
                    ]),
            ])->columns(1);
    }
}
