<?php

namespace App\Filament\Resources\Contributors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class ContributorForm
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
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create'),

                        TextInput::make('country')
                            ->maxLength(100),

                        TextInput::make('language')
                            ->maxLength(50),

                        Select::make('locale')
                            ->options(['ar' => 'Arabic', 'en' => 'English'])
                            ->default('ar'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Writer Profile')
                    ->relationship('contributor')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('profile_photo')
                            ->label('Profile Photo')
                            ->image()
                            ->disk('images')
                            ->directory('contributors/photos')
                            ->columnSpanFull(),

                        Textarea::make('bio')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('portfolio_link')
                            ->label('Portfolio Link')
                            ->url()
                            ->maxLength(255),

                        Select::make('categories')
                            ->label('Writing Categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
