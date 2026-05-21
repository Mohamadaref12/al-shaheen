<?php

namespace App\Filament\Resources\TrainingCourses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingCourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Details')
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

                        Select::make('category')
                            ->options([
                                'journalism'   => 'Journalism',
                                'media'        => 'Media',
                                'writing'      => 'Writing',
                                'photography'  => 'Photography',
                                'broadcasting' => 'Broadcasting',
                            ])
                            ->required(),

                        Select::make('level')
                            ->options([
                                'beginner'     => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced'     => 'Advanced',
                            ])
                            ->required(),

                        Toggle::make('is_premium')
                            ->label('Premium'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->directory('courses')
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
