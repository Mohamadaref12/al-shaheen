<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->required(),

                        TextInput::make('location')
                            ->maxLength(255),

                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->required(),

                        DateTimePicker::make('ends_at')
                            ->label('Ends At'),

                        TextInput::make('external_url')
                            ->label('External URL')
                            ->url()
                            ->maxLength(500),

                        Toggle::make('is_featured')
                            ->label('Featured'),

                        Textarea::make('description')
                            ->rows(5)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Event Image')
                            ->image()
                            ->directory('events')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
