<?php

namespace App\Filament\Resources\News\Schemas;

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

class NewsForm
{
    use HasTranslatableContentFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('News')
                    ->tabs([
                        Tab::make('Setup')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema([
                                Section::make('News Details')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('author_id')
                                            ->label('Author')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->required(),

                                        Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->searchable(),

                                        TextInput::make('read_time')
                                            ->label('Read Time (minutes)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(5),

                                        Select::make('status')
                                            ->options([
                                                'draft'        => 'Draft',
                                                'under_review' => 'Under Review',
                                                'published'    => 'Published',
                                                'archived'     => 'Archived',
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

                                Section::make('Media')
                                    ->schema([
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
                            ]),

                        Tab::make('English')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema(self::translatableContentFields('en', 'news_translations')),

                        Tab::make('Arabic')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema(self::translatableContentFields('ar', 'news_translations')),

                        Tab::make('SEO')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema(self::translatableSeoSections('news_translations')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
