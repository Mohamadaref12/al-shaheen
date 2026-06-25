<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('News Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('subtitle')
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

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable(),

                        Select::make('locale')
                            ->options(['ar' => 'Arabic', 'en' => 'English'])
                            ->required(),

                        TextInput::make('read_time')
                            ->label('Read Time (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->default(5),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'under_review' => 'Under Review',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),

                        Toggle::make('is_breaking')
                            ->label('Breaking News'),

                        Toggle::make('is_premium')
                            ->label('Premium'),

                        DateTimePicker::make('published_at')
                            ->label('Published At'),
                    ]),

                Section::make('Content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->columnSpanFull(),

                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->disk('images')
                            ->directory('news')
                            ->columnSpanFull(),

                        TextInput::make('video_embed')
                            ->label('Video Embed URL')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')
                            ->maxLength(200)
                            ->columnSpanFull(),

                        Textarea::make('seo_description')
                            ->rows(2)
                            ->maxLength(400)
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
