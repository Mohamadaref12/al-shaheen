<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page Details')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->required()->columnSpanFull(),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->columnSpanFull(),
                    RichEditor::make('content')->columnSpanFull(),

                    Select::make('locale')
                        ->options(['ar' => 'Arabic', 'en' => 'English'])
                        ->required(),

                    Toggle::make('is_active')->label('Active')->default(true),
                ]),
        ])->columns(1);
    }
}
