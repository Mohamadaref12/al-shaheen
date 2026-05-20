<?php

namespace App\Filament\Resources\ContentSubmissions\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('subtitle')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Select::make('writer_id')
                            ->label('Writer')
                            ->relationship('writer', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('reviewer_id')
                            ->label('Reviewer')
                            ->relationship('reviewer', 'name')
                            ->searchable(),

                        Select::make('type')
                            ->options([
                                'article' => 'Article',
                                'report'  => 'Report',
                                'op_ed'   => 'Op-Ed',
                            ])
                            ->required(),

                        Select::make('status')
                            ->options([
                                'pending'  => 'Pending',
                                'review'   => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),

                        RichEditor::make('content')
                            ->columnSpanFull(),

                        Textarea::make('reviewer_notes')
                            ->label('Reviewer Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
