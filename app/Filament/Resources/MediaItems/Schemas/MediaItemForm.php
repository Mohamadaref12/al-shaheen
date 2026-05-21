<?php

namespace App\Filament\Resources\MediaItems\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Media Details')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->required()->columnSpanFull(),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->columnSpanFull(),
                    Textarea::make('description')->columnSpanFull(),

                    Select::make('type')
                        ->options([
                            'video'   => 'Video',
                            'audio'   => 'Audio',
                            'gallery' => 'Gallery',
                        ])
                        ->required(),

                    TextInput::make('media_url')
                        ->label('Media URL')
                        ->required()
                        ->columnSpanFull(),

                    FileUpload::make('thumbnail')
                        ->image()
                        ->directory('media/thumbnails'),

                    TextInput::make('duration_seconds')
                        ->label('Duration (seconds)')
                        ->numeric(),

                    Textarea::make('transcript')->columnSpanFull(),

                    Select::make('author_id')
                        ->label('Author')
                        ->relationship('author', 'name')
                        ->searchable()
                        ->required(),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable(),

                    Select::make('locale')
                        ->options(['ar' => 'Arabic', 'en' => 'English'])
                        ->required(),

                    Select::make('status')
                        ->options([
                            'draft'     => 'Draft',
                            'published' => 'Published',
                            'archived'  => 'Archived',
                        ])
                        ->required(),

                    Toggle::make('is_premium')->label('Premium'),
                    DateTimePicker::make('published_at'),
                ]),
        ])->columns(1);
    }
}
