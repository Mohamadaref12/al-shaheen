<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Schemas\Concerns\HasTranslatableContentFields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ArticleForm
{
    use HasTranslatableContentFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Article')
                    ->tabs([
                        Tab::make('Setup')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Section::make('Article Details')
                                    ->columns(2)
                                    ->schema([
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
                                            ->minValue(1)
                                            ->default(5),

                                        Toggle::make('is_breaking')
                                            ->label('Breaking News'),

                                        DateTimePicker::make('published_at')
                                            ->label('Published At'),
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

                                Section::make('Media')
                                    ->schema([
                                        FileUpload::make('featured_image')
                                            ->label('Featured Image')
                                            ->image()
                                            ->disk('images')
                                            ->directory('articles')
                                            ->columnSpanFull(),

                                        TextInput::make('video_embed')
                                            ->label('Video Embed URL')
                                            ->url()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('English')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema(self::translatableContentFields('en', 'article_translations', 'Title', 'Article Body')),

                        Tab::make('Arabic')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema(self::translatableContentFields('ar', 'article_translations', 'Title', 'Article Body')),

                        Tab::make('SEO')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema(self::translatableSeoSections('article_translations')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
