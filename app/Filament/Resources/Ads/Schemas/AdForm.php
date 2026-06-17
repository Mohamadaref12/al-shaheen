<?php

namespace App\Filament\Resources\Ads\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ad Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Select::make('placement')
                            ->options([
                                'leaderboard'  => 'Leaderboard (728×90)',
                                'hero'         => 'Hero Takeover',
                                'in_feed'      => 'In-Feed Native',
                                'mid_article'  => 'Mid-Article (300×250)',
                                'right_rail'   => 'Right Rail (300×600)',
                                'footer'       => 'Footer (728×90)',
                            ])
                            ->required(),

                        Select::make('ad_category')
                            ->label('Category')
                            ->options([
                                'general'  => 'General',
                                'premium'  => 'Premium',
                                'local'    => 'Local',
                                'regional' => 'Regional',
                            ]),

                        TextInput::make('link_url')
                            ->label('Link URL')
                            ->url()
                            ->maxLength(500),

                        DateTimePicker::make('starts_at')
                            ->label('Starts At'),

                        DateTimePicker::make('ends_at')
                            ->label('Ends At'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        FileUpload::make('image_url')
                            ->label('Ad Image')
                            ->image()
                            ->disk('images')
                            ->directory('ads')
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }
}
