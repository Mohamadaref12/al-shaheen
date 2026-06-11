<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Details')
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

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('locale')
                            ->options(['ar' => 'Arabic', 'en' => 'English'])
                            ->required(),

                        Select::make('status')
                            ->options([
                                'draft'     => 'Draft',
                                'published' => 'Published',
                                'archived'  => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),

                        Toggle::make('is_premium')
                            ->label('Premium'),

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

                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->disk('images')
                            ->directory('reports')
                            ->columnSpanFull(),

                        TextInput::make('file_url')
                            ->label('Report File URL')
                            ->url()
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
