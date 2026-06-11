<?php

namespace App\Filament\Resources\Writers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class WriterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation) => $operation === 'create'),

                        Select::make('locale')
                            ->options(['ar' => 'Arabic', 'en' => 'English'])
                            ->default('ar')
                            ->required(),

                        TextInput::make('country')
                            ->maxLength(100),

                        TextInput::make('language')
                            ->maxLength(50),
                    ]),

                Section::make('Profile')
                    ->columns(2)
                    ->schema([
                        TextInput::make('display_name')
                            ->maxLength(255),

                        FileUpload::make('profile_photo')
                            ->image()
                            ->disk('images')
                            ->directory('writers/photos')
                            ->columnSpanFull(),

                        Textarea::make('bio')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('portfolio_link')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('location')
                            ->maxLength(255),

                        TextInput::make('media_affiliation')
                            ->label('Media Affiliation')
                            ->maxLength(255),
                    ]),

                Section::make('Experience & Specialties')
                    ->columns(2)
                    ->schema([
                        Select::make('experience_level')
                            ->options([
                                'junior'  => 'Junior',
                                'mid'     => 'Mid-level',
                                'senior'  => 'Senior',
                                'expert'  => 'Expert',
                            ]),

                        TextInput::make('languages')
                            ->label('Languages (comma-separated)')
                            ->helperText('e.g. Arabic, English'),

                        TextInput::make('editorial_specialties')
                            ->label('Editorial Specialties (comma-separated)'),

                        FileUpload::make('id_verification_file')
                            ->label('ID Verification File')
                            ->disk('images')
                            ->directory('writers/verification')
                            ->columnSpanFull(),

                        KeyValue::make('social_links')
                            ->label('Social Links')
                            ->keyLabel('Platform')
                            ->valueLabel('URL')
                            ->columnSpanFull(),

                        KeyValue::make('sample_publications')
                            ->label('Sample Publications')
                            ->keyLabel('Title')
                            ->valueLabel('URL')
                            ->columnSpanFull(),
                    ]),

                Section::make('Application')
                    ->columns(2)
                    ->schema([
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

                        Toggle::make('is_verified_writer')
                            ->label('Verified Writer'),

                        Textarea::make('reviewer_notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}