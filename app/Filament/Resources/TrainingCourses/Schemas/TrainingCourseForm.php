<?php

namespace App\Filament\Resources\TrainingCourses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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

                        Select::make('course_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
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

                        Textarea::make('excerpt')
                            ->label('Short Description')
                            ->helperText('Shown under the course title on the course page.')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Summary')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->disk('images')
                            ->directory('courses')
                            ->columnSpanFull(),

                        FileUpload::make('about_image')
                            ->label('About Section Image')
                            ->image()
                            ->disk('images')
                            ->directory('courses/about')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing & Enrollment')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('USD')
                            ->minValue(0),

                        TextInput::make('original_price')
                            ->label('Original Price')
                            ->numeric()
                            ->prefix('USD')
                            ->minValue(0),

                        TextInput::make('currency')
                            ->default('USD')
                            ->maxLength(3),

                        Toggle::make('has_lifetime_access')
                            ->label('Lifetime Access')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Instructor')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instructor_name')
                            ->maxLength(255),

                        TextInput::make('instructor_label')
                            ->label('Instructor Subtitle')
                            ->helperText('e.g. Group Sessions')
                            ->maxLength(255),

                        FileUpload::make('instructor_avatar')
                            ->label('Instructor Avatar')
                            ->image()
                            ->disk('images')
                            ->directory('courses/instructors')
                            ->columnSpanFull(),
                    ]),

                Section::make('Course Stats')
                    ->columns(3)
                    ->schema([
                        TextInput::make('duration_weeks')
                            ->label('Duration (weeks)')
                            ->numeric()
                            ->minValue(1),

                        TextInput::make('downloadable_files_count')
                            ->label('Downloadable Files')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('video_preview_url')
                            ->label('Video Preview URL')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Section::make('About & Learning Outcomes')
                    ->schema([
                        RichEditor::make('about_content')
                            ->label('About the Course')
                            ->columnSpanFull(),

                        Repeater::make('learning_outcomes')
                            ->label('What You Will Learn')
                            ->simple(
                                TextInput::make('outcome')->required()
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('Reviews')
                    ->columns(2)
                    ->schema([
                        TextInput::make('rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1),

                        TextInput::make('reviews_count')
                            ->label('Reviews Count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
            ])->columns(1);
    }
}
