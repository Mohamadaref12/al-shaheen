<?php

namespace App\Filament\Resources\Writers\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WriterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        TextInput::make('display_name')
                            ->maxLength(255),

                        Textarea::make('bio')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('portfolio_link')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('location')
                            ->maxLength(100),
                    ]),

                Section::make('Expertise')
                    ->columns(2)
                    ->schema([
                        Select::make('experience_level')
                            ->options([
                                'beginner'     => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'senior'       => 'Senior',
                                'expert'       => 'Expert',
                            ]),

                        Select::make('application_status')
                            ->options([
                                'draft'        => 'Draft',
                                'submitted'    => 'Submitted',
                                'under_review' => 'Under Review',
                                'approved'     => 'Approved',
                                'rejected'     => 'Rejected',
                                'suspended'    => 'Suspended',
                            ])
                            ->required(),

                        TextInput::make('media_affiliation')
                            ->maxLength(255),

                        TextInput::make('id_verification')
                            ->label('ID Verification')
                            ->maxLength(255),
                    ]),
            ]);
    }
}