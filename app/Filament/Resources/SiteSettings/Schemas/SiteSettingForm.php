<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Setting')
                ->schema([
                    TextInput::make('key')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    KeyValue::make('value')
                        ->label('Value (JSON)'),
                ]),
        ])->columns(1);
    }
}
