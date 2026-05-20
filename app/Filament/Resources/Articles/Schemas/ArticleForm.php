<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article Details')
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

                        Select::make('primary_category_id')
                            ->label('Primary Category')
                            ->relationship('primaryCategory', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('locale')
                            ->options(['ar' => 'Arabic', 'en' => 'English'])
                            ->required(),

                        Select::make('status')
                            ->options([
                                'draft'     => 'Draft',
                                'review'    => 'Under Review',
                                'published' => 'Published',
                                'archived'  => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),

                        TextInput::make('read_time')
                            ->label('Read Time (minutes)')
                            ->numeric()
                            ->minValue(1),

                        Toggle::make('is_breaking')
                            ->label('Breaking News'),

                        DateTimePicker::make('published_at')
                            ->label('Published At'),
                    ]),

                Section::make('Content')
                    ->schema([
                        RichEditor::make('content')
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('video_embed')
                            ->label('Video Embed URL')
                            ->url()
                            ->columnSpanFull(),

                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->directory('articles')
                            ->columnSpanFull(),
                    ]),

                Section::make('Taxonomy')
                    ->schema([
                        Select::make('secondaryCategories')
                            ->label('Secondary Categories')
                            ->relationship('secondaryCategories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
