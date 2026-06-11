<?php

namespace App\Filament\Resources\Interviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InterviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Guest Info')
                ->columns(2)
                ->schema([
                    TextInput::make('guest_name')->required()->columnSpanFull(),
                    TextInput::make('guest_title')->columnSpanFull(),
                    FileUpload::make('guest_photo')
                        ->image()
                        ->disk('images')
                        ->directory('interviews/guests'),
                ]),

            Section::make('Interview Details')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->required()->columnSpanFull(),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->columnSpanFull(),
                    Textarea::make('excerpt')->columnSpanFull(),
                    RichEditor::make('content')->columnSpanFull(),
                    TextInput::make('video_embed')->label('Video Embed URL')->columnSpanFull(),
                    FileUpload::make('featured_image')
                        ->image()
                        ->disk('images')
                        ->directory('interviews'),

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
                            'draft'        => 'Draft',
                            'under_review' => 'Under Review',
                            'published'    => 'Published',
                            'archived'     => 'Archived',
                        ])
                        ->required(),

                    Toggle::make('is_premium')->label('Premium'),
                    DateTimePicker::make('published_at'),
                ]),
        ])->columns(1);
    }
}
